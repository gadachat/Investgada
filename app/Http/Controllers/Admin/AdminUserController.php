<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\SecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * User management list with search and filters.
     */
    public function index(Request $request)
    {
        $query = User::with(['wallets', 'rank', 'sponsor']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('referral_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $stats = [
            'total'     => User::count(),
            'active'    => User::where('status', 'active')->count(),
            'suspended' => User::where('status', 'suspended')->count(),
            'banned'    => User::where('status', 'banned')->count(),
            'verified'  => User::where('kyc_status', 'verified')->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * View a single user's full profile + wallets + transactions.
     */
    public function show(User $user)
    {
        $user->load(['wallets', 'rank', 'sponsor', 'referrals']);

        // Recent transactions
        $transactions = Transaction::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Deposits
        $deposits = DB::table('deposits')->where('user_id', $user->id)
            ->orderByDesc('created_at')->limit(10)->get();

        // Withdrawals
        $withdrawals = DB::table('withdrawals')->where('user_id', $user->id)
            ->orderByDesc('created_at')->limit(10)->get();

        // Investments
        $investments = DB::table('investments')->where('user_id', $user->id)
            ->orderByDesc('created_at')->limit(10)->get();

        // Binary info
        $binaryNode = DB::table('binary_tree')->where('user_id', $user->id)->first();

        // Direct referrals
        $directReferrals = User::where('sponsor_id', $user->id)
            ->select('id', 'name', 'email', 'status', 'total_invested', 'created_at')
            ->limit(20)->get();

        return view('admin.users.show', compact(
            'user', 'transactions', 'deposits', 'withdrawals', 'investments',
            'binaryNode', 'directReferrals'
        ));
    }

    /**
     * Admin sends test/virtual funds to a user's wallet.
     */
    public function sendFunds(Request $request, User $user)
    {
        $request->validate([
            'wallet_type' => ['required', 'in:deposit,interest,commission,bonus,withdrawal'],
            'amount'      => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'note'        => ['nullable', 'string', 'max:500'],
        ]);

        $wallet = Wallet::where('user_id', $user->id)
            ->where('type', $request->wallet_type)
            ->first();

        if (!$wallet) {
            // Auto-create
            $wallet = Wallet::create([
                'user_id'  => $user->id,
                'type'     => $request->wallet_type,
                'currency' => 'USD',
                'balance'  => 0,
            ]);
        }

        DB::transaction(function () use ($wallet, $user, $request) {
            // Credit wallet
            $wallet->credit((float) $request->amount);

            // Create transaction record
            Transaction::create([
                'reference'     => 'ADM-' . strtoupper(\Illuminate\Support\Str::random(12)),
                'user_id'       => $user->id,
                'wallet_id'     => $wallet->id,
                'type'          => 'admin_fund',
                'direction'     => 'credit',
                'amount'        => $request->amount,
                'balance_after' => $wallet->fresh()->balance,
                'currency'      => 'USD',
                'description'   => $request->note ?: 'Admin test funds',
                'metadata'      => json_encode([
                    'admin_id'  => auth()->id(),
                    'admin_name' => auth()->user()->name,
                    'is_test_fund' => true,
                ]),
                'status'        => 'completed',
            ]);

            // Notification
            DB::table('notifications')->insert([
                'user_id'    => $user->id,
                'type'       => 'admin_fund',
                'title'      => 'Funds Received',
                'message'    => "You received $" . number_format($request->amount, 2) . " in your {$request->wallet_type} wallet.",
                'link'       => '/dashboard/wallet',
                'is_read'    => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        SecurityLog::log(
            action: 'admin_sent_funds',
            module: 'user_management',
            description: "Admin sent \${$request->amount} to user {$user->name} ({$user->email}) — {$request->wallet_type} wallet",
            severity: 'warning',
            metadata: [
                'user_id'     => $user->id,
                'wallet_type' => $request->wallet_type,
                'amount'      => $request->amount,
                'note'        => $request->note,
            ]
        );

        return back()->with('success', "Sent $" . number_format($request->amount, 2) . " to {$user->name}'s {$request->wallet_type} wallet.");
    }

    /**
     * Admin deducts funds from a user's wallet (reverse of sendFunds).
     */
    public function deductFunds(Request $request, User $user)
    {
        $request->validate([
            'wallet_type' => ['required', 'in:deposit,interest,commission,bonus,withdrawal'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'note'        => ['nullable', 'string', 'max:500'],
        ]);

        $wallet = Wallet::where('user_id', $user->id)
            ->where('type', $request->wallet_type)
            ->first();

        if (!$wallet || $wallet->balance < $request->amount) {
            return back()->with('error', "Insufficient balance in {$request->wallet_type} wallet.");
        }

        DB::transaction(function () use ($wallet, $user, $request) {
            $wallet->debit((float) $request->amount);

            Transaction::create([
                'reference'     => 'ADM-DBT-' . strtoupper(\Illuminate\Support\Str::random(10)),
                'user_id'       => $user->id,
                'wallet_id'     => $wallet->id,
                'type'          => 'admin_deduction',
                'direction'     => 'debit',
                'amount'        => $request->amount,
                'balance_after' => $wallet->fresh()->balance,
                'currency'      => 'USD',
                'description'   => $request->note ?: 'Admin deduction',
                'metadata'      => json_encode([
                    'admin_id' => auth()->id(),
                    'admin_name' => auth()->user()->name,
                ]),
                'status'        => 'completed',
            ]);
        });

        SecurityLog::log(
            action: 'admin_deducted_funds',
            module: 'user_management',
            description: "Admin deducted \${$request->amount} from user {$user->name} — {$request->wallet_type} wallet",
            severity: 'danger',
            metadata: [
                'user_id'     => $user->id,
                'wallet_type' => $request->wallet_type,
                'amount'      => $request->amount,
            ]
        );

        return back()->with('success', "Deducted $" . number_format($request->amount, 2) . " from {$user->name}'s {$request->wallet_type} wallet.");
    }

    /**
     * Update user status (active/suspended/banned).
     */
    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => ['required', 'in:active,inactive,suspended,banned'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $oldStatus = $user->status;
        $user->update(['status' => $request->status]);

        SecurityLog::log(
            action: 'user_status_changed',
            module: 'user_management',
            description: "Admin changed user {$user->name} status from {$oldStatus} to {$request->status}" . ($request->reason ? ": {$request->reason}" : ''),
            severity: $request->status === 'banned' ? 'danger' : 'warning',
            metadata: [
                'user_id'    => $user->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'reason'     => $request->reason,
            ]
        );

        // Notify user
        if ($request->status !== $oldStatus) {
            DB::table('notifications')->insert([
                'user_id'    => $user->id,
                'type'       => 'account_status',
                'title'      => 'Account Status Updated',
                'message'    => "Your account status has been changed to: {$request->status}" . ($request->reason ? ". Reason: {$request->reason}" : ''),
                'is_read'    => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', "User status updated to {$request->status}.");
    }

    /**
     * Update user role.
     */
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', 'in:user,admin,super_admin'],
        ]);

        if ($user->id === auth()->id() && $request->role !== 'super_admin') {
            return back()->with('error', 'You cannot demote yourself.');
        }

        $oldRole = $user->role;
        $user->update([
            'role'     => $request->role,
            'is_admin' => in_array($request->role, ['admin', 'super_admin']),
        ]);

        SecurityLog::log(
            action: 'user_role_changed',
            module: 'user_management',
            description: "Admin changed user {$user->name} role from {$oldRole} to {$request->role}",
            severity: 'danger',
            metadata: ['user_id' => $user->id, 'old_role' => $oldRole, 'new_role' => $request->role]
        );

        return back()->with('success', "User role updated to {$request->role}.");
    }

    /**
     * Update user's applicant type (user, marketer, leader).
     * Marketers and leaders can apply for fund programs.
     */
    public function updateApplicantType(Request $request, User $user)
    {
        $request->validate([
            'applicant_type' => 'required|in:user,marketer,leader',
        ]);

        $oldType = $user->applicant_type;
        $user->update([
            'applicant_type' => $request->applicant_type,
        ]);

        SecurityLog::log(
            action: 'user_applicant_type_changed',
            module: 'user_management',
            description: "Admin changed user {$user->name} applicant type from {$oldType} to {$request->applicant_type}",
            severity: 'warning',
            metadata: ['user_id' => $user->id, 'old_type' => $oldType, 'new_type' => $request->applicant_type]
        );

        return back()->with('success', "User applicant type updated to {$request->applicant_type}.");
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
            'notify_user'      => ['nullable', 'boolean'],
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        SecurityLog::log(
            action: 'admin_reset_password',
            module: 'user_management',
            description: "Admin reset password for user {$user->name} ({$user->email})",
            severity: 'danger',
            metadata: ['user_id' => $user->id]
        );

        if ($request->boolean('notify_user')) {
            DB::table('notifications')->insert([
                'user_id'    => $user->id,
                'type'       => 'security',
                'title'      => 'Password Reset',
                'message'    => 'Your password has been reset by an administrator. If this was not authorized, please contact support immediately.',
                'is_read'    => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Password reset successfully.');
    }
}
