<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Display all notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $filter = $request->get('filter', 'all'); // all, unread, read
        $type = $request->get('type', 'all'); // all, deposit, withdrawal, investment, referral, profit, system, kyc, support

        $query = DB::table('notifications')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($filter === 'read') {
            $query->where('is_read', true);
        }

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $notifications = (clone $query)->paginate(20);
        $unreadCount = (clone $query)->where('is_read', false)->count();

        // Group by type for the sidebar filter counts
        $typeCounts = DB::table('notifications')
            ->where('user_id', $user->id)
            ->select('type', DB::raw('count(*) as total'), DB::raw('sum(case when is_read = false then 1 else 0 end) as unread'))
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        return view('dashboard.notifications.index', compact('notifications', 'unreadCount', 'typeCounts', 'filter', 'type'));
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, $id)
    {
        $user = $request->user();

        $updated = DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->update(['is_read' => true, 'read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'updated' => $updated]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllRead(Request $request)
    {
        $user = $request->user();

        $count = DB::table('notifications')
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'marked' => $count]);
        }

        return back()->with('success', $count . ' notifications marked as read.');
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification deleted.');
    }

    /**
     * Clear all read notifications.
     */
    public function clearRead(Request $request)
    {
        $user = $request->user();

        $count = DB::table('notifications')
            ->where('user_id', $user->id)
            ->where('is_read', true)
            ->delete();

        return back()->with('success', $count . ' read notifications cleared.');
    }

    /**
     * Fetch recent notifications for the dropdown / bell icon (AJAX).
     */
    public function recent(Request $request)
    {
        $user = $request->user();

        $notifications = DB::table('notifications')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'message' => \Str::limit($n->message, 100),
                    'is_read' => (bool) $n->is_read,
                    'icon' => $this->iconFor($n->type),
                    'color' => $this->colorFor($n->type),
                    'time_ago' => $n->created_at ? \Carbon\Carbon::parse($n->created_at)->diffForHumans() : '',
                    'created_at' => $n->created_at,
                ];
            });

        $unreadCount = DB::table('notifications')
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    private function iconFor($type)
    {
        return match ($type) {
            'deposit' => 'fa-arrow-down',
            'withdrawal' => 'fa-arrow-up',
            'investment' => 'fa-chart-line',
            'referral' => 'fa-users',
            'profit' => 'fa-coins',
            'kyc' => 'fa-id-card',
            'support' => 'fa-life-ring',
            'rank' => 'fa-trophy',
            'system' => 'fa-bell',
            default => 'fa-info-circle',
        };
    }

    private function colorFor($type)
    {
        return match ($type) {
            'deposit' => '#3b82f6',
            'withdrawal' => '#ef4444',
            'investment' => '#6366f1',
            'referral' => '#a855f7',
            'profit' => '#10b981',
            'kyc' => '#f59e0b',
            'support' => '#06b6d4',
            'rank' => '#f43f5e',
            'system' => '#7c3aed',
            default => '#64748b',
        };
    }
}
