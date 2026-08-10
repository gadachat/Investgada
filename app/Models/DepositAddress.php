<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositAddress extends Model
{
    protected $table = 'deposit_addresses';
    protected $fillable = [
        'network', 'coin', 'address', 'qr_code', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getActive(string $network, string $coin = 'USDT'): ?self
    {
        return static::where('network', $network)
            ->where('coin', $coin)
            ->where('is_active', true)
            ->first();
    }
}
