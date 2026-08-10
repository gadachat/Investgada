<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycVerification extends Model
{
    protected $table = 'kyc_verifications';

    protected $fillable = [
        'user_id',
        'document_type',
        'document_number',
        'first_name',
        'last_name',
        'date_of_birth',
        'nationality',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'phone_number',
        'id_front_path',
        'id_back_path',
        'proof_of_address_path',
        'selfie_path',
        'status',              // pending, verified, rejected
        'submitted_at',
        'verified_at',
        'verified_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
    ];

    protected $casts = [
        'date_of_birth'   => 'date',
        'submitted_at'     => 'datetime',
        'verified_at'     => 'datetime',
        'rejected_at'     => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /*
    |---------------------------------------------------------------------------
    | Scopes
    |---------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
