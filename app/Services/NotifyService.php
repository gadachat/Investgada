<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Centralized email + in-app notification service.
 * Sends emails AND creates in-app notification records in one call.
 */
class NotifyService
{
    /**
     * Send a notification (email + in-app).
     *
     * @param User $user
     * @param string $type    Notification type key (deposit_confirmed, withdrawal_processed, etc.)
     * @param string $title   Short title
     * @param string $message Body message
     * @param array  $data    Extra data for email template
     * @param bool   $sendEmail Whether to also send email
     */
    public static function send(User $user, string $type, string $title, string $message, array $data = [], bool $sendEmail = true): void
    {
        // 1. Create in-app notification
        Notification::create([
            'user_id' => $user->id,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'data'    => json_encode($data),
            'is_read' => false,
        ]);

        // 2. Send email
        if ($sendEmail && $user->email) {
            try {
                Mail::send('emails.generic', [
                    'user'    => $user,
                    'title'   => $title,
                    'message' => $message,
                    'data'    => $data,
                    'type'    => $type,
                ], function ($mail) use ($user, $title) {
                    $mail->to($user->email, $user->name)
                         ->subject($title . ' — ' . config('app.name', 'Platform'));
                });
            } catch (\Exception $e) {
                Log::warning("Failed to send email to {$user->email}: {$e->getMessage()}");
            }
        }
    }

    // ── Convenience methods for common events ──

    public static function depositConfirmed(User $user, $amount, $method): void
    {
        self::send(
            $user, 'deposit_confirmed', 'Deposit Confirmed',
            "Your deposit of \${$amount} via {$method} has been confirmed and credited to your wallet.",
            ['amount' => $amount, 'method' => $method, 'action' => 'deposit']
        );
    }

    public static function depositPending(User $user, $amount, $method): void
    {
        self::send(
            $user, 'deposit_pending', 'Deposit Pending',
            "Your deposit of \${$amount} via {$method} is being processed. You will be notified once confirmed.",
            ['amount' => $amount, 'method' => $method, 'action' => 'deposit']
        );
    }

    public static function withdrawalRequested(User $user, $amount, $method): void
    {
        self::send(
            $user, 'withdrawal_requested', 'Withdrawal Request Submitted',
            "Your withdrawal request for \${$amount} via {$method} has been submitted and is pending review.",
            ['amount' => $amount, 'method' => $method, 'action' => 'withdrawal']
        );
    }

    public static function withdrawalProcessed(User $user, $amount, $method): void
    {
        self::send(
            $user, 'withdrawal_processed', 'Withdrawal Processed',
            "Your withdrawal of \${$amount} via {$method} has been processed and sent.",
            ['amount' => $amount, 'method' => $method, 'action' => 'withdrawal']
        );
    }

    public static function withdrawalRejected(User $user, $amount, $reason): void
    {
        self::send(
            $user, 'withdrawal_rejected', 'Withdrawal Rejected',
            "Your withdrawal request for \${$amount} was rejected. Reason: {$reason}",
            ['amount' => $amount, 'reason' => $reason, 'action' => 'withdrawal']
        );
    }

    public static function investmentActivated(User $user, $packageName, $amount, $expectedReturn): void
    {
        self::send(
            $user, 'investment_activated', 'Investment Activated',
            "Your investment in {$packageName} (\${$amount}) is now active. Expected return: \${$expectedReturn}.",
            ['package' => $packageName, 'amount' => $amount, 'expected_return' => $expectedReturn, 'action' => 'investment']
        );
    }

    public static function investmentCompleted(User $user, $packageName, $totalEarned): void
    {
        self::send(
            $user, 'investment_completed', 'Investment Completed',
            "Your investment in {$packageName} has completed. Total earned: \${$totalEarned}.",
            ['package' => $packageName, 'earned' => $totalEarned, 'action' => 'investment']
        );
    }

    public static function tradeClosed(User $user, $symbol, $pnl, $isWin): void
    {
        $result = $isWin ? 'profit' : 'loss';
        self::send(
            $user, 'trade_closed', 'Trade Closed',
            "Your {$symbol} trade has closed with a {$result} of \${$pnl}.",
            ['symbol' => $symbol, 'pnl' => $pnl, 'is_win' => $isWin, 'action' => 'trade'],
            false // Don't email for every trade close (too noisy)
        );
    }

    public static function rankUpgraded(User $user, $rankName): void
    {
        self::send(
            $user, 'rank_upgraded', 'Rank Upgraded!',
            "Congratulations! You've been promoted to {$rankName}. New benefits are now available.",
            ['rank' => $rankName, 'action' => 'rank']
        );
    }

    public static function referralJoined(User $user, $referralName): void
    {
        self::send(
            $user, 'referral_joined', 'New Referral',
            "{$referralName} has joined using your referral link.",
            ['referral_name' => $referralName, 'action' => 'referral'],
            false // In-app only
        );
    }

    public static function commissionEarned(User $user, $amount, $type): void
    {
        self::send(
            $user, 'commission_earned', 'Commission Earned',
            "You earned \${$amount} in {$type} commission.",
            ['amount' => $amount, 'type' => $type, 'action' => 'commission'],
            false // In-app only
        );
    }

    public static function kycApproved(User $user): void
    {
        self::send(
            $user, 'kyc_approved', 'KYC Verified',
            "Your KYC verification has been approved. You now have full access to withdrawals and trading.",
            ['action' => 'kyc']
        );
    }

    public static function kycRejected(User $user, $reason): void
    {
        self::send(
            $user, 'kyc_rejected', 'KYC Rejected',
            "Your KYC submission was rejected. Reason: {$reason}. Please resubmit with correct documents.",
            ['reason' => $reason, 'action' => 'kyc']
        );
    }

    public static function fundApproved(User $user, $amount): void
    {
        self::send(
            $user, 'fund_approved', 'Fund Application Approved',
            "Your fund application for \${$amount} has been approved and credited to your account.",
            ['amount' => $amount, 'action' => 'fund']
        );
    }

    public static function signalReceived(User $user, $symbol, $direction, $entry, $sl, $tp): void
    {
        $dir = strtoupper($direction);
        self::send(
            $user, 'trading_signal', "New {$dir} Signal: {$symbol}",
            "Signal: {$dir} {$symbol} @ Entry {$entry}, SL {$sl}, TP {$tp}.",
            ['symbol' => $symbol, 'direction' => $direction, 'entry' => $entry, 'sl' => $sl, 'tp' => $tp, 'action' => 'signal']
        );
    }

    /**
     * Broadcast to all users (admin usage).
     */
    public static function broadcast(string $title, string $message, array $data = []): void
    {
        $users = User::where('status', 'active')->where('is_admin', false)->get();
        foreach ($users as $user) {
            self::send($user, 'broadcast', $title, $message, $data, true);
        }
    }
}
