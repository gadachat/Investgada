<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Wallet extends Model
{
    protected $fillable = [
        'user_id', 'type', 'currency', 'balance', 'locked_balance',
    ];

    protected $casts = [
        'balance' => 'decimal:8',
        'locked_balance' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Credit funds to this wallet
    public function credit(float $amount): bool
    {
        return $this->increment('balance', $amount) > 0;
    }

    // Debit funds from this wallet (returns false if insufficient) — atomic
    public function debit(float $amount): bool
    {
        if ((float) $this->balance < $amount) {
            return false;
        }
        return DB::transaction(function () use ($amount) {
            return $this->decrement('balance', $amount) > 0;
        });
    }

    // Lock funds (move from balance to locked_balance) — atomic
    public function lock(float $amount): bool
    {
        if ((float) $this->balance < $amount) {
            return false;
        }
        return DB::transaction(function () use ($amount) {
            $this->decrement('balance', $amount);
            $this->increment('locked_balance', $amount);
            return true;
        });
    }

    // Unlock funds (move from locked_balance back to balance) — atomic
    public function unlock(float $amount): bool
    {
        if ((float) $this->locked_balance < $amount) {
            return false;
        }
        return DB::transaction(function () use ($amount) {
            $this->decrement('locked_balance', $amount);
            $this->increment('balance', $amount);
            return true;
        });
    }

    // Available balance (not locked)
    public function getAvailableAttribute(): string
    {
        return bcsub($this->balance, $this->locked_balance, 8);
    }
}
