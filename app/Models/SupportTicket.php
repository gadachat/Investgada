<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'ticket_number', 'user_id', 'subject', 'category',
        'priority', 'status', 'assigned_to', 'closed_at',
        'rating', 'rating_comment', 'rated_at',
    ];

    protected $casts = [
        'closed_at'  => 'datetime',
        'rated_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(SupportTicketMessage::class, 'ticket_id');
    }
}
