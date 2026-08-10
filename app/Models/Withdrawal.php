<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'method', 'currency', 'amount',
        'fee', 'net_amount', 'wallet_address', 'network',
        'bank_account_name', 'bank_account_number', 'bank_name',
        'bank_routing', 'bank_country', 'status', 'admin_note',
        'approved_by', 'approved_at', 'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'fee' => 'decimal:8',
        'net_amount' => 'decimal:8',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
