<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'username', 'email', 'phone', 'country', 'password', 'avatar',
        'role', 'status', 'sponsor_id', 'parent_id', 'binary_position',
        'referral_code', 'referred_by_code', 'rank_id', 'kyc_status', 'is_admin',
        'total_invested', 'total_earned', 'total_withdrawn', 'total_referral_earnings',
        'two_factor_secret', 'two_factor_enabled', 'two_factor_verified_at', 'two_factor_recovery_codes',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'two_factor_verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = static::generateUniqueReferralCode();
            }
        });
    }

    public static function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    public function getReferralLink(): string
    {
        return url('/register?ref=' . ($this->referral_code ?? ''));
    }

    // --- Relationships ---

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    public function wallet($type = 'deposit')
    {
        return $this->hasOne(Wallet::class)->where('type', $type);
    }

    public function sponsor()
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referredBy()
    {
        return $this->hasOne(Referral::class, 'referred_id');
    }

    public function binaryNode()
    {
        return $this->hasOne(BinaryTree::class);
    }

    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    public function investments()
    {
        return $this->hasMany(Investment::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function kycSubmissions()
    {
        return $this->hasMany(KycSubmission::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function web3Wallets()
    {
        return $this->hasMany(Web3Wallet::class, 'user_id');
    }

    public function primaryWeb3Wallet()
    {
        return $this->hasOne(Web3Wallet::class, 'user_id')->where('is_primary', true);
    }

    // --- Helpers ---

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getBalance($type = 'deposit'): string
    {
        return $this->wallet($type)?->balance ?? '0';
    }
}
