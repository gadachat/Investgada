<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\KycVerification;
use App\Models\Setting;
use App\Models\Notification;

class KycController extends Controller
{
    /**
     * Display KYC status page
     */
    public function index()
    {
        $user     = auth()->user();
        $kyc      = KycVerification::where('user_id', $user->id)->latest()->first();
        $kycEnabled = Setting::get('kyc_module', true);

        // Build status timeline
        $timeline = [];
        if ($kyc) {
            $timeline[] = ['step' => 'Submitted',    'date' => $kyc->created_at, 'done' => true];
            $timeline[] = ['step' => 'Under Review', 'date' => $k->submitted_at ?? $kyc->created_at, 'done' => in_array($kyc->status, ['pending','verified','rejected'])];
            $timeline[] = ['step' => 'Approved',     'date' => $kyc->verified_at, 'done' => $kyc->status === 'verified'];
        }

        // Requirements text from settings
        $requirements = [
            'Government-issued ID (passport, driver\'s license, or national ID card)',
            'Proof of address (utility bill or bank statement, not older than 3 months)',
            'Selfie holding your ID document',
            'All documents must be clear, colored, and unexpired',
        ];

        $rejectionReason = $kyc && $kyc->status === 'rejected' ? $kyc->rejection_reason : null;

        return view('dashboard.kyc.index', compact('kyc', 'kycEnabled', 'timeline', 'requirements', 'rejectionReason'));
    }

    /**
     * Show submission form
     */
    public function create()
    {
        $user = auth()->user();
        $kycEnabled = Setting::get('kyc_module', true);

        if (!$kycEnabled) {
            return redirect()->route('dashboard.kyc.index')->with('error', 'KYC verification is currently disabled.');
        }

        $existing = KycVerification::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'verified'])
            ->latest()
            ->first();

        if ($existing && $existing->status === 'verified') {
            return redirect()->route('dashboard.kyc.index')->with('error', 'Your KYC is already verified.');
        }

        if ($existing && $existing->status === 'pending') {
            return redirect()->route('dashboard.kyc.index')->with('error', 'Your KYC is already under review.');
        }

        $documentTypes = [
            'passport'    => 'Passport',
            'driver_license' => 'Driver\'s License',
            'national_id' => 'National ID Card',
            'voter_card'  => 'Voter\'s Card',
        ];

        return view('dashboard.kyc.create', compact('documentTypes'));
    }

    /**
     * Store KYC submission
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $kycEnabled = Setting::get('kyc_module', true);

        if (!$kycEnabled) {
            return redirect()->route('dashboard.kyc.index')->with('error', 'KYC verification is currently disabled.');
        }

        $existing = KycVerification::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'verified'])
            ->latest()
            ->first();

        if ($existing) {
            return redirect()->route('dashboard.kyc.index')->with('error', 'You already have a pending or verified KYC.');
        }

        $maxSize = (int) Setting::get('kyc_max_file_size', 2048); // KB

        $validated = $request->validate([
            'document_type'      => 'required|in:passport,driver_license,national_id,voter_card',
            'document_number'    => 'required|string|max:100',
            'first_name'         => 'required|string|max:100',
            'last_name'          => 'required|string|max:100',
            'date_of_birth'      => 'required|date|before:today',
            'nationality'        => 'required|string|max:100',
            'address_line_1'     => 'required|string|max:255',
            'address_line_2'     => 'nullable|string|max:255',
            'city'               => 'required|string|max:100',
            'state'              => 'required|string|max:100',
            'postal_code'        => 'nullable|string|max:20',
            'country'            => 'required|string|max:100',
            'phone_number'        => 'required|string|max:30',
            'id_front'           => "required|file|mimes:jpg,jpeg,png,pdf|max:{$maxSize}",
            'id_back'            => 'required|file|mimes:jpg,jpeg,png,pdf|max:' . $maxSize,
            'proof_of_address'   => 'required|file|mimes:jpg,jpeg,png,pdf|max:' . $maxSize,
            'selfie'             => 'required|file|mimes:jpg,jpeg,png|max:' . $maxSize,
        ]);

        // Store files
        $uploadPath = 'kyc/' . $user->id;
        $idFrontPath       = $request->file('id_front')->store($uploadPath, 'private');
        $idBackPath        = $request->file('id_back')->store($uploadPath, 'private');
        $proofAddressPath  = $request->file('proof_of_address')->store($uploadPath, 'private');
        $selfiePath        = $request->file('selfie')->store($uploadPath, 'private');

        $kyc = KycVerification::create([
            'user_id'           => $user->id,
            'document_type'     => $validated['document_type'],
            'document_number'   => $validated['document_number'],
            'first_name'        => $validated['first_name'],
            'last_name'         => $validated['last_name'],
            'date_of_birth'     => $validated['date_of_birth'],
            'nationality'       => $validated['nationality'],
            'address_line_1'    => $validated['address_line_1'],
            'address_line_2'    => $validated['address_line_2'] ?? null,
            'city'              => $validated['city'],
            'state'             => $validated['state'],
            'postal_code'       => $validated['postal_code'] ?? null,
            'country'           => $validated['country'],
            'phone_number'      => $validated['phone_number'],
            'id_front_path'     => $idFrontPath,
            'id_back_path'      => $idBackPath,
            'proof_of_address_path' => $proofAddressPath,
            'selfie_path'       => $selfiePath,
            'status'            => 'pending',
            'submitted_at'      => now(),
        ]);

        // Notify all admins
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id'  => $admin->id,
                'type'     => 'system',
                'title'    => 'New KYC Submission',
                'message'  => "{$user->name} has submitted KYC verification documents for review.",
                'link'     => route('admin.kyc.show', $kyc->id),
                'is_read'  => false,
            ]);
        }

        // Notify user
        Notification::create([
            'user_id'  => $user->id,
            'type'     => 'kyc',
            'title'    => 'KYC Submitted',
            'message'  => 'Your KYC documents have been submitted successfully. We will review them within 24-48 hours.',
            'is_read'  => false,
        ]);

        return redirect()->route('dashboard.kyc.index')->with('success', 'KYC documents submitted successfully. We will review them within 24-48 hours.');
    }

    /**
     * Download a KYC document (user's own)
     */
    public function downloadDocument(Request $request, $id, $type)
    {
        $user = auth()->user();
        $kyc = KycVerification::where('user_id', $user->id)->findOrFail($id);

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
