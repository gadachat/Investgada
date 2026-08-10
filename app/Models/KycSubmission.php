<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KycSubmission extends Model
{
    protected $fillable = [
        'user_id', 'full_name', 'document_type', 'document_number',
        'country', 'date_of_birth', 'address',
        'front_document', 'back_document', 'selfie', 'proof_of_address',
        'status', 'rejection_reason', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
