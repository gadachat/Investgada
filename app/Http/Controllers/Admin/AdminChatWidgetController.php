<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class AdminChatWidgetController extends Controller
{
    /**
     * Show Tawk.to settings page
     */
    public function index()
    {
        $settings = [
            'tawk_enabled'      => Setting::get('tawk_enabled', false),
            'tawk_property_id'   => Setting::get('tawk_property_id', ''),
            'tawk_widget_id'     => Setting::get('tawk_widget_id', 'default'),
            'tawk_show_on_admin'  => Setting::get('tawk_show_on_admin', false),
        ];

        return view('admin.settings.chat-widget', compact('settings'));
    }

    /**
     * Update Tawk.to settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'tawk_property_id'   => 'nullable|string|max:100',
            'tawk_widget_id'     => 'nullable|string|max:100',
            'tawk_enabled'       => 'nullable|boolean',
            'tawk_show_on_admin' => 'nullable|boolean',
        ]);

        Setting::set('tawk_enabled',       $request->has('tawk_enabled'));
        Setting::set('tawk_show_on_admin', $request->has('tawk_show_on_admin'));
        Setting::set('tawk_property_id',   $request->input('tawk_property_id', ''));
        Setting::set('tawk_widget_id',     $request->input('tawk_widget_id', 'default') ?: 'default');

        return back()->with('success', 'Tawk.to chat widget settings updated.');
    }

    /**
     * Toggle Tawk.to on/off (AJAX)
     */
    public function toggle(Request $request)
    {
        $request->validate(['enabled' => 'required|boolean']);

        Setting::set('tawk_enabled', $request->boolean('enabled'));

        return response()->json([
            'success' => true,
            'message' => 'Chat widget ' . ($request->boolean('enabled') ? 'enabled' : 'disabled') . '.',
            'enabled' => $request->boolean('enabled'),
        ]);
    }
}
