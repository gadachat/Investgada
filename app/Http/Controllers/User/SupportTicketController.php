<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\SecurityLog;
use App\Mail\TicketCreated;
use App\Mail\TicketReplied;
use App\Mail\TicketClosed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SupportTicketController extends Controller
{
    /**
     * List all tickets for the logged-in user.
     */
    public function index()
    {
        $tickets = SupportTicket::where('user_id', auth()->id())
            ->orderByDesc('updated_at')
            ->paginate(10);

        $stats = [
            'open'    => SupportTicket::where('user_id', auth()->id())->whereIn('status', ['open', 'answered', 'pending'])->count(),
            'closed'  => SupportTicket::where('user_id', auth()->id())->where('status', 'closed')->count(),
            'total'   => SupportTicket::where('user_id', auth()->id())->count(),
        ];

        return view('dashboard.support.index', compact('tickets', 'stats'));
    }

    /**
     * Show the create ticket form.
     */
    public function create()
    {
        $categories = [
            'general'     => 'General Inquiry',
            'deposit'     => 'Deposit Issue',
            'withdrawal'  => 'Withdrawal Issue',
            'investment'  => 'Investment Question',
            'account'     => 'Account Problem',
            'technical'   => 'Technical Support',
            'referral'    => 'Referral / MLM',
        ];

        return view('dashboard.support.create', compact('categories'));
    }

    /**
     * Store a new ticket.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject'  => ['required', 'string', 'max:200'],
            'category' => ['required', 'in:general,deposit,withdrawal,investment,account,technical,referral'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'message'  => ['required', 'string', 'max:5000'],
            'attachments.*' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt,zip'],
        ]);

        $ticketNumber = 'TK-' . strtoupper(Str::random(8));

        $ticket = SupportTicket::create([
            'ticket_number' => $ticketNumber,
            'user_id'       => auth()->id(),
            'subject'       => $request->subject,
            'category'      => $request->category,
            'priority'      => $request->priority,
            'status'        => 'open',
        ]);

        // Handle file attachments
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

        SupportTicketMessage::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => auth()->id(),
            'message'     => $request->message,
            'is_admin'    => false,
            'attachments' => $attachments ?: null,
        ]);

        SecurityLog::log(
            action: 'ticket_created',
            module: 'support',
            description: "User created ticket {$ticketNumber}: {$request->subject}",
            severity: 'info',
            metadata: ['ticket_id' => $ticket->id, 'category' => $request->category]
        );

        // Send email to user (confirmation)
        try {
            Mail::to(auth()->user()->email)->send(new TicketCreated($ticket));
        } catch (\Exception $e) {
            // Fail silently — email is not critical
        }

        return redirect()->route('dashboard.support.show', $ticket)
            ->with('success', "Ticket {$ticketNumber} created. We'll respond shortly.");
    }

    /**
     * View a single ticket with its message thread.
     */
    public function show(SupportTicket $ticket)
    {
        if ($ticket->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $messages = $ticket->messages()->with('user')->orderBy('created_at')->get();

        return view('dashboard.support.show', compact('ticket', 'messages'));
    }

    /**
     * Reply to a ticket.
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        if ($ticket->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($ticket->status === 'closed') {
            return back()->with('error', 'This ticket is closed. Create a new one if you need further help.');
        }

        $request->validate([
            'message'        => ['required', 'string', 'max:5000'],
            'attachments.*'  => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt,zip'],
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
            'is_admin'    => false,
            'attachments' => $attachments ?: null,
        ]);

        // Reopen ticket if it was answered
        if ($ticket->status === 'answered') {
            $ticket->update(['status' => 'open']);
        }

        $ticket->touch();

        // Notify admins (send to all admin users)
        try {
            $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->pluck('email')->toArray();
            foreach ($admins as $email) {
                Mail::to($email)->send(new TicketReplied($ticket, $message, false));
            }
        } catch (\Exception $e) {
            // Fail silently
        }

        return back()->with('success', 'Your reply has been sent.');
    }

    /**
     * User closes their own ticket.
     */
    public function close(SupportTicket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        $ticket->update([
            'status'    => 'closed',
            'closed_at' => now(),
        ]);

        SecurityLog::log(
            action: 'ticket_closed',
            module: 'support',
            description: "User closed ticket {$ticket->ticket_number}",
            severity: 'info',
            metadata: ['ticket_id' => $ticket->id]
        );

        // Send closure email
        try {
            Mail::to(auth()->user()->email)->send(new TicketClosed($ticket));
        } catch (\Exception $e) {
            // Fail silently
        }

        return redirect()->route('dashboard.support.index')
            ->with('success', "Ticket {$ticket->ticket_number} closed.");
    }

    /**
     * Rate a closed ticket (satisfaction rating).
     */
    public function rate(Request $request, SupportTicket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        if ($ticket->status !== 'closed') {
            return back()->with('error', 'Only closed tickets can be rated.');
        }

        $request->validate([
            'rating'         => ['required', 'integer', 'min:1', 'max:5'],
            'rating_comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $ticket->update([
            'rating'         => $request->rating,
            'rating_comment' => $request->rating_comment,
            'rated_at'       => now(),
        ]);

        return back()->with('success', 'Thank you for your feedback!');
    }
}
