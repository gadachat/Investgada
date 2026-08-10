<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicketMessage extends Model
{
    protected $fillable = [
        'ticket_id', 'user_id', 'message', 'attachments', 'is_admin',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_admin' => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
