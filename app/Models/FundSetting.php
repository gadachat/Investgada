<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundSetting extends Model
{
    protected $table = 'fund_settings';

    protected $fillable = ['key', 'value'];

    public $timestamps = true;

    /**
     * Get a fund setting value.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a fund setting value.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? 'true' : 'false') : (string) $value]
        );
    }

    /**
     * Get all settings as key-value array.
     */
    public static function allSettings(): array
    {
        return static::pluck('value', 'key')->toArray();
    }

    /**
     * Check if fund program is enabled.
     */
    public static function isEnabled(): bool
    {
        return self::get('fund_program_enabled', 'true') === 'true';
    }

    /**
     * Check if a specific withdrawal rule allows the type.
     */
    public static function allowsWithdrawalType(string $type): bool
    {
        $key = match ($type) {
            'commission' => 'allow_commission_withdrawal',
            'profit'     => 'allow_profit_withdrawal',
            'capital'    => 'allow_capital_withdrawal',
            default      => 'allow_commission_withdrawal',
        };

        return self::get($key, 'false') === 'true';
    }
}
