<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'wallet_id', 'type', 'direction',
        'amount', 'balance_after', 'currency', 'description',
        'metadata', 'status',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'balance_after' => 'decimal:8',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function isCredit(): bool
    {
        return $this->direction === 'credit';
    }

    public function isDebit(): bool
    {
        return $this->direction === 'debit';
    }
}
