<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\Request;

class AdminAnnouncementController extends Controller
{
    public function index()
    {
        $now = now();

        $stats = [
            'total' => Announcement::count(),
            'active' => Announcement::active()->count(),
            'scheduled' => Announcement::where('is_active', true)
                ->where('starts_at', '>', $now)
                ->count(),
            'expired' => Announcement::whereNotNull('ends_at')
                ->where('ends_at', '<', $now)
                ->count(),
        ];

        $announcements = Announcement::with(['creator', 'targetUser'])
            ->latest()
            ->paginate(15);

        return view('admin.announcements.index', compact('announcements', 'stats'));
    }

    public function create()
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        return view('admin.announcements.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,danger,maintenance',
            'target' => 'required|in:all,verified,investors,traders,specific',
            'target_user_id' => 'nullable|required_if:target,specific|exists:users,id',
            'is_active' => 'boolean',
            'is_dismissible' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : false;
        $validated['is_dismissible'] = $request->has('is_dismissible') ? (bool) $request->is_dismissible : false;
        $validated['created_by'] = auth()->id();

        if ($validated['target'] !== 'specific') {
            $validated['target_user_id'] = null;
        }

        Announcement::create($validated);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement created successfully.');
    }

    public function edit(Announcement $announcement)
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        return view('admin.announcements.edit', compact('announcement', 'users'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,danger,maintenance',
            'target' => 'required|in:all,verified,investors,traders,specific',
            'target_user_id' => 'nullable|required_if:target,specific|exists:users,id',
            'is_active' => 'boolean',
            'is_dismissible' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : false;
        $validated['is_dismissible'] = $request->has('is_dismissible') ? (bool) $request->is_dismissible : false;

        if ($validated['target'] !== 'specific') {
            $validated['target_user_id'] = null;
        }

        $announcement->update($validated);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement deleted successfully.');
    }

    public function toggle(Announcement $announcement)
    {
        $announcement->update([
            'is_active' => !$announcement->is_active,
        ]);

        return redirect()->back()->with('success', 'Announcement status updated successfully.');
    }
}
