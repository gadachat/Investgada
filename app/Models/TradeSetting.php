<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeSetting extends Model
{
    protected $table = 'trade_settings';

    protected $fillable = ['key', 'value'];

    public $timestamps = true;

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? 'true' : 'false') : (string) $value]
        );
    }

    public static function allSettings(): array
    {
        return static::pluck('value', 'key')->toArray();
    }

    public static function isEnabled(): bool
    {
        return self::get('trading_enabled', 'true') === 'true';
    }

    public static function maxLeverage(): int
    {
        return (int) self::get('max_leverage', 0);
    }
}
