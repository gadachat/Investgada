<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminNotificationController extends Controller
{
    /**
     * Admin notifications index — view all + send broadcast.
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $type = $request->get('type', 'all');

        $query = DB::table('notifications')->orderBy('created_at', 'desc');

        if ($filter === 'unread') {
            $query->where('is_read', false);
        }

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $notifications = $query->paginate(25);
        $totalSent = DB::table('notifications')->count();
        $unreadTotal = DB::table('notifications')->where('is_read', false)->count();

        // User count for broadcast targeting
        $userCount = DB::table('users')->where('is_admin', false)->count();
        $activeUsers = DB::table('users')->where('is_admin', false)->where('status', 'active')->count();

        // Notification templates
        $templates = DB::table('notification_templates')->orderBy('name')->get();

        return view('admin.notifications.index', compact(
            'notifications', 'totalSent', 'unreadTotal', 'userCount', 'activeUsers', 'templates', 'filter', 'type'
        ));
    }

    /**
     * Send a broadcast notification to users.
     */
    public function broadcast(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'type' => 'required|in:system,deposit,withdrawal,investment,referral,profit,kyc,support,rank',
            'target' => 'required|in:all,active,inactive,investors',
            'link' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Determine recipients
        $recipientsQuery = DB::table('users')->where('is_admin', false);

        switch ($request->target) {
            case 'active':
                $recipientsQuery->where('status', 'active');
                break;
            case 'inactive':
                $recipientsQuery->where('status', '!=', 'active');
                break;
            case 'investors':
                $recipientsQuery->whereIn('id', function ($q) {
                    $q->select('user_id')->from('investments')->where('status', 'active');
                });
                break;
        }

        $recipients = $recipientsQuery->get(['id']);

        $now = now();
        $rows = $recipients->map(function ($user) use ($request, $now) {
            return [
                'user_id' => $user->id,
                'type' => $request->type,
                'title' => $request->title,
                'message' => $request->message,
                'link' => $request->link,
                'is_read' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        // Bulk insert
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('notifications')->insert($chunk);
        }

        return back()->with('success', 'Broadcast sent to ' . count($recipients) . ' users.');
    }

    /**
     * Save a reusable notification template.
     */
    public function storeTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'type' => 'required|in:system,deposit,withdrawal,investment,referral,profit,kyc,support,rank',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::table('notification_templates')->insert([
            'name' => $request->name,
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Template saved.');
    }

    /**
     * Delete a notification template.
     */
    public function deleteTemplate($id)
    {
        DB::table('notification_templates')->where('id', $id)->delete();
        return back()->with('success', 'Template deleted.');
    }

    /**
     * Delete a notification.
     */
    public function destroy($id)
    {
        DB::table('notifications')->where('id', $id)->delete();
        return back()->with('success', 'Notification deleted.');
    }
}
