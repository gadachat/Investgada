<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\KycVerification;
use App\Models\User;
use App\Models\Setting;
use App\Models\Notification;

class AdminKycController extends Controller
{
    /**
     * List all KYC submissions
     */
    public function index(Request $request)
    {
        $query = KycVerification::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('document_number', 'like', "%{$search}%");
        }

        $kycs = $query->latest()->paginate(15);

        // Stats
        $stats = [
            'total'     => KycVerification::count(),
            'pending'   => KycVerification::where('status', 'pending')->count(),
            'verified'  => KycVerification::where('status', 'verified')->count(),
            'rejected'  => KycVerification::where('status', 'rejected')->count(),
        ];

        $kycEnabled = Setting::get('kyc_module', true);

        return view('admin.kyc.index', compact('kycs', 'stats', 'kycEnabled'));
    }

    /**
     * Show single KYC submission detail
     */
    public function show($id)
    {
        $kyc = KycVerification::with('user')->findOrFail($id);

        $documentTypes = [
            'passport'        => 'Passport',
            'driver_license'  => "Driver's License",
            'national_id'     => 'National ID Card',
            'voter_card'      => "Voter's Card",
        ];

        return view('admin.kyc.show', compact('kyc', 'documentTypes'));
    }

    /**
     * Approve KYC
     */
    public function approve(Request $request, $id)
    {
        $kyc = KycVerification::findOrFail($id);

        if ($kyc->status !== 'pending') {
            return back()->with('error', 'This KYC has already been processed.');
        }

        $kyc->update([
            'status'       => 'verified',
            'verified_at'  => now(),
            'verified_by'  => auth()->id(),
            'rejection_reason' => null,
        ]);

        // Mark user as verified
        User::where('id', $kyc->user_id)->update(['kyc_verified' => true]);

        // Notify user
        Notification::create([
            'user_id'  => $kyc->user_id,
            'type'     => 'kyc',
            'title'    => 'KYC Verified',
            'message'  => 'Congratulations! Your KYC verification has been approved. You now have full access to all platform features.',
            'is_read'  => false,
        ]);

        return redirect()->route('admin.kyc.index')NotifyService::kycApproved($kyc->user ?? $kyc->submission->user);

        return redirect()->back()->with('success', 'KYC approved successfully. User has been notified.');
    }

    /**
     * Reject KYC with reason
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $kyc = KycVerification::findOrFail($id);

        if ($kyc->status !== 'pending') {
            return back()->with('error', 'This KYC has already been processed.');
        }

        $kyc->update([
            'status'            => 'rejected',
            'rejected_at'       => now(),
            'rejected_by'       => auth()->id(),
            'rejection_reason'  => $request->rejection_reason,
        ]);

        // Notify user
        Notification::create([
            'user_id'  => $kyc->user_id,
            'type'     => 'kyc',
            'title'    => 'KYC Rejected',
            'message'  => "Your KYC submission was rejected. Reason: {$request->rejection_reason}. Please correct the issues and resubmit.",
            'is_read'  => false,
        ]);

        return redirect()->route('admin.kyc.index')->with('success', 'KYC rejected. User has been notified with the reason.');
    }

    /**
     * Toggle KYC module on/off (AJAX)
     */
    public function toggle(Request $request)
    {
        $request->validate(['enabled' => 'required|boolean']);

        Setting::set('kyc_module', $request->boolean('enabled'));

        return response()->json([
            'success' => true,
            'message' => 'KYC module ' . ($request->boolean('enabled') ? 'enabled' : 'disabled') . '.',
            'enabled' => $request->boolean('enabled'),
        ]);
    }

    /**
     * Download KYC document (admin)
     */
    public function downloadDocument($id, $type)
    {
        $kyc = KycVerification::findOrFail($id);

        $pathMap = [
            'id_front'         => $kyc->id_front_path,
            'id_back'          => $kyc->id_back_path,
            'proof_of_address' => $kyc->proof_of_address_path,
            'selfie'           => $kyc->selfie_path,
        ];

        if (!isset($pathMap[$type]) || !$pathMap[$type]) {
            abort(404, 'Document not found.');
        }

        if (!Storage::disk('private')->exists($pathMap[$type])) {
            abort(404, 'File not found on disk.');
        }

        return response()->download(Storage::disk('private')->path($pathMap[$type]));
    }
}
