<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureSetting;
use App\Models\PlatformSetting;
use App\Models\Rank;
use App\Models\DepositAddress;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    // ========== FEATURE ON/OFF MANAGER ==========
    public function features()
    {
        $features = FeatureSetting::orderBy('key')->get();
        return view('admin.settings.features', compact('features'));
    }

    public function toggleFeature(Request $request)
    {
        $request->validate([
            'key' => 'required|exists:feature_settings,key',
            'is_enabled' => 'required|boolean',
        ]);

        $feature = FeatureSetting::where('key', $request->key)->first();
        $feature->update(['is_enabled' => $request->is_enabled]);

        return response()->json([
            'success' => true,
            'message' => $feature->label . ' has been ' . ($request->is_enabled ? 'enabled' : 'disabled') . '.',
            'is_enabled' => $request->is_enabled,
        ]);
    }

    /**
     * Return a feature's current config as JSON (for the config editor modal).
     */
    public function getFeatureConfig(FeatureSetting $feature)
    {
        $config = $feature->config;

        // If config is stored as ['value' => '...'], unwrap it
        if (is_array($config) && count($config) === 1 && isset($config['value'])) {
            $configString = $config['value'];
        } elseif (is_array($config)) {
            $configString = json_encode($config, JSON_PRETTY_PRINT);
        } else {
            $configString = is_string($config) ? $config : '';
        }

        return response()->json([
            'success' => true,
            'config'  => $configString,
        ]);
    }

    public function updateFeatureConfig(Request $request, FeatureSetting $feature)
    {
        $request->validate([
            'config' => 'nullable|string|max:5000',
        ]);

        $rawConfig = $request->input('config');

        // Handle empty config
        if (empty(trim($rawConfig))) {
            $feature->update(['config' => null]);
            return back()->with('success', 'Feature configuration cleared.');
        }

        // Try to decode as JSON — if valid, store as array; otherwise store as plain string
        $decoded = json_decode($rawConfig, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $feature->update(['config' => $decoded]);
        } else {
            // Not valid JSON — store as-is wrapped so the json cast doesn't break
            $feature->update(['config' => ['value' => $rawConfig]]);
        }

        return back()->with('success', "Configuration for '{$feature->label}' updated successfully.");
    }

    // ========== PLATFORM SETTINGS ==========
    public function platform()
    {
        $settings = PlatformSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');
        return view('admin.settings.platform', compact('settings'));
    }

    public function updatePlatform(Request $request)
    {
        $request->validate(['settings' => 'required|array']);

        foreach ($request->settings as $key => $value) {
            PlatformSetting::set($key, $value);
        }

        return back()->with('success', 'Platform settings updated successfully.');
    }

    // ========== RANKS MANAGEMENT ==========
    public function ranks()
    {
        $ranks = Rank::orderBy('sort_order')->get();
        return view('admin.settings.ranks', compact('ranks'));
    }

    public function storeRank(Request $request)
    {
        $request->validate([
            'name'                   => 'required|string|max:50',
            'badge_color'            => 'required|string|max:7',
            'min_investment'         => 'nullable|numeric|min:0',
            'min_direct_referrals'   => 'nullable|integer|min:0',
            'min_team_volume'        => 'nullable|numeric|min:0',
            'matching_bonus_percent' => 'required|numeric|min:0|max:100',
            'direct_referral_percent'=> 'required|numeric|min:0|max:100',
            'profit_share_percent'   => 'nullable|numeric|min:0|max:100',
            'salary_bonus'           => 'nullable|numeric|min:0',
            'sort_order'             => 'nullable|integer',
        ]);

        Rank::create([
            'name'                   => $request->name,
            'slug'                   => \Illuminate\Support\Str::slug($request->name),
            'badge_color'            => $request->badge_color,
            'min_investment'         => $request->min_investment ?? 0,
            'min_direct_referrals'   => $request->min_direct_referrals ?? 0,
            'min_team_volume'        => $request->min_team_volume ?? 0,
            'matching_bonus_percent' => $request->matching_bonus_percent,
            'direct_referral_percent'=> $request->direct_referral_percent,
            'profit_share_percent'   => $request->profit_share_percent ?? 0,
            'salary_bonus'           => $request->salary_bonus ?? 0,
            'is_active'              => true,
            'sort_order'             => $request->sort_order ?? 0,
        ]);

        return back()->with('success', 'Rank created successfully.');
    }

    public function updateRank(Request $request, Rank $rank)
    {
        $rank->update($request->only([
            'name', 'badge_color', 'min_investment', 'min_direct_referrals',
            'min_team_volume', 'matching_bonus_percent', 'direct_referral_percent',
            'profit_share_percent', 'salary_bonus', 'is_active', 'sort_order',
        ]));
        return back()->with('success', 'Rank updated successfully.');
    }

    // ========== DEPOSIT ADDRESSES ==========
    public function addresses()
    {
        $addresses = DepositAddress::orderBy('network')->orderBy('coin')->get();
        return view('admin.settings.addresses', compact('addresses'));
    }

    public function storeAddress(Request $request)
    {
        $request->validate([
            'network' => 'required|string|max:20',
            'coin'    => 'required|string|max:10',
            'address' => 'required|string|max:200',
            'qr_code' => 'nullable|string',
        ]);

        DepositAddress::create($request->only(['network', 'coin', 'address', 'qr_code', 'is_active']));
        return back()->with('success', 'Deposit address added.');
    }

    public function toggleAddress(DepositAddress $address)
    {
        $address->update(['is_active' => !$address->is_active]);
        return back()->with('success', 'Address ' . ($address->is_active ? 'enabled' : 'disabled') . '.');
    }

    public function destroyAddress(DepositAddress $address)
    {
        $address->delete();
        return back()->with('success', 'Address deleted.');
    }
}
