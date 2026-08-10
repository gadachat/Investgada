<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'method', 'currency', 'amount',
        'fee', 'net_amount', 'tx_hash', 'from_address', 'to_address',
        'network', 'bank_reference', 'admin_note', 'payment_proof',
        'status', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'fee' => 'decimal:8',
        'net_amount' => 'decimal:8',
        'payment_proof' => 'array',
        'approved_at' => 'datetime',
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
