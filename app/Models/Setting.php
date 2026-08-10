<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Setting extends Model
{
    protected $table = 'platform_settings';

    protected $fillable = ['key', 'value', 'type', 'group'];

    public $timestamps = true;

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        $value = $setting->value;
        if ($setting->type === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }
        if ($setting->type === 'integer') {
            return (int) $value;
        }
        if ($setting->type === 'json') {
            return json_decode($value, true);
        }
        return $value;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, $value, string $type = 'string', string $group = 'general'): void
    {
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
            $type = 'boolean';
        } elseif (is_array($value)) {
            $value = json_encode($value);
            $type = 'json';
        } elseif (is_int($value)) {
            $type = 'integer';
        } else {
            $value = (string) $value;
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group]
        );
    }
}
