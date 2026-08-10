<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureSetting extends Model
{
    protected $fillable = [
        'key', 'label', 'is_enabled', 'description', 'config',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'config' => 'array',
    ];

    public static function isEnabled(string $key): bool
    {
        return (bool) static::where('key', $key)->value('is_enabled');
    }
}
