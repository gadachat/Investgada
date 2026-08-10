<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\FeatureSetting;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function active()
    {
        if (!FeatureSetting::isEnabled('announcements')) {
            return response()->json(['success' => true, 'announcements' => []]);
        }

        $user = auth()->user();
        $dismissed = session('dismissed_announcements', []);

        $announcements = Announcement::active()
            ->forUser($user)
            ->whereNotIn('id', $dismissed)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'announcements' => $announcements,
        ]);
    }

    public function dismiss(Request $request)
    {
        $request->validate([
            'announcement_id' => 'required|integer|exists:announcements,id',
        ]);

        $dismissed = session()->get('dismissed_announcements', []);
        $announcementId = (int) $request->input('announcement_id');

        if (!in_array($announcementId, $dismissed)) {
            $dismissed[] = $announcementId;
            session()->put('dismissed_announcements', $dismissed);
        }

        return response()->json([
            'success' => true,
            'message' => 'Announcement dismissed.',
            'dismissed' => $dismissed,
        ]);
    }
}
