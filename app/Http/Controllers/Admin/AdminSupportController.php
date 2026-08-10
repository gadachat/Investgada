<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\SecurityLog;
use App\Mail\TicketReplied;
use App\Mail\TicketClosed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminSupportController extends Controller
{
    /**
     * Admin ticket inbox — all tickets with filtering.
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'assignedTo']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $tickets = $query->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 END")
            ->orderByDesc('updated_at')
            ->paginate(20)->withQueryString();

        $stats = [
            'open'     => SupportTicket::whereIn('status', ['open', 'pending'])->count(),
            'answered' => SupportTicket::where('status', 'answered')->count(),
            'urgent'   => SupportTicket::where('priority', 'urgent')->whereIn('status', ['open', 'pending', 'answered'])->count(),
            'closed'   => SupportTicket::where('status', 'closed')->count(),
            'total'    => SupportTicket::count(),
            'avg_rating' => SupportTicket::whereNotNull('rating')->avg('rating'),
        ];

        return view('admin.support.index', compact('tickets', 'stats'));
    }

    /**
     * View a ticket with its full thread.
     */
    public function show(SupportTicket $ticket)
    {
        $messages = $ticket->messages()->with('user')->orderBy('created_at')->get();

        // Mark as "answered" when admin views
        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'answered']);
        }

        // Get admin users for assignment
        $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->orderBy('name')->get();

        return view('admin.support.show', compact('ticket', 'messages', 'admins'));
    }

    /**
     * Admin replies to a ticket.
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message'       => ['required', 'string', 'max:5000'],
            'attachments.*' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt,zip'],
        ]);

        // Handle attachments
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments/' . $ticket->id, 'public');
                $attachments[] = [
                    'path'      => $path,
                    'name'      => $file->getClientOriginalName(),
                    'size'      => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        $message = SupportTicketMessage::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => auth()->id(),
            'message'     => $request->message,
            'is_admin'     => true,
            'attachments' => $attachments ?: null,
        ]);

        $ticket->update([
            'status'      => 'answered',
            'assigned_to' => $ticket->assigned_to ?? auth()->id(),
        ]);

        $ticket->touch();

        SecurityLog::log(
            action: 'ticket_replied',
            module: 'support',
            description: "Admin replied to ticket {$ticket->ticket_number}: {$ticket->subject}",
            severity: 'info',
            metadata: ['ticket_id' => $ticket->id, 'user_id' => $ticket->user_id]
        );

        // Notify the ticket owner
        try {
            Mail::to($ticket->user->email)->send(new TicketReplied($ticket, $message, true));
        } catch (\Exception $e) {
            // Fail silently
        }

        return back()->with('success', 'Reply sent.');
    }

    /**
     * Assign a ticket to an admin.
     */
    public function assign(Request $request, SupportTicket $ticket)
    {
        $request->validate(['assigned_to' => 'required|exists:users,id']);

        $ticket->update(['assigned_to' => $request->assigned_to]);

        SecurityLog::log(
            action: 'ticket_assigned',
            module: 'support',
            description: "Ticket {$ticket->ticket_number} assigned",
            severity: 'info',
            metadata: ['ticket_id' => $ticket->id, 'assigned_to' => $request->assigned_to]
        );

        return back()->with('success', 'Ticket assigned.');
    }

    /**
     * Quick assign to current admin.
     */
    public function assignToMe(SupportTicket $ticket)
    {
        $ticket->update(['assigned_to' => auth()->id()]);

        SecurityLog::log(
            action: 'ticket_assigned',
            module: 'support',
            description: "Ticket {$ticket->ticket_number} self-assigned",
            severity: 'info',
            metadata: ['ticket_id' => $ticket->id, 'assigned_to' => auth()->id()]
        );

        return back()->with('success', 'Ticket assigned to you.');
    }

    /**
     * Admin closes a ticket.
     */
    public function close(SupportTicket $ticket)
    {
        $ticket->update([
            'status'    => 'closed',
            'closed_at' => now(),
        ]);

        SecurityLog::log(
            action: 'ticket_closed_admin',
            module: 'support',
            description: "Admin closed ticket {$ticket->ticket_number}",
            severity: 'warning',
            metadata: ['ticket_id' => $ticket->id, 'user_id' => $ticket->user_id]
        );

        // Send closure email to user
        try {
            Mail::to($ticket->user->email)->send(new TicketClosed($ticket));
        } catch (\Exception $e) {
            // Fail silently
        }

        return back()->with('success', "Ticket {$ticket->ticket_number} closed.");
    }

    /**
     * Reopen a closed ticket.
     */
    public function reopen(SupportTicket $ticket)
    {
        $ticket->update([
            'status'    => 'open',
            'closed_at' => null,
        ]);

        SecurityLog::log(
            action: 'ticket_reopened',
            module: 'support',
            description: "Admin reopened ticket {$ticket->ticket_number}",
            severity: 'info',
            metadata: ['ticket_id' => $ticket->id]
        );

        return back()->with('success', "Ticket {$ticket->ticket_number} reopened.");
    }

    /**
     * Change ticket priority.
     */
    public function updatePriority(Request $request, SupportTicket $ticket)
    {
        $request->validate(['priority' => 'required|in:low,medium,high,urgent']);

        $ticket->update(['priority' => $request->priority]);

        SecurityLog::log(
            action: 'ticket_priority_changed',
            module: 'support',
            description: "Ticket {$ticket->ticket_number} priority set to {$request->priority}",
            severity: 'info',
            metadata: ['ticket_id' => $ticket->id, 'old' => $ticket->priority, 'new' => $request->priority]
        );

        return back()->with('success', 'Priority updated.');
    }
}
