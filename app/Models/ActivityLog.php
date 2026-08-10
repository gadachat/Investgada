<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'ip_address',
        'user_agent',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * User relationship (the user affected or performing the action)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Admin relationship (the admin performing the action, if applicable)
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Polymorphic subject relationship (optional)
     */
    public function subject()
    {
        if ($this->subject_type && $this->subject_id) {
            return $this->morphTo('subject', 'subject_type', 'subject_id');
        }
        return null;
    }
}
