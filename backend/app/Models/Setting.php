<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    /**
     * Get setting value by key.
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        if ($setting->type === 'boolean') {
            return $setting->value === '1';
        }

        if ($setting->type === 'integer') {
            return (int) $setting->value;
        }

        if ($setting->type === 'json') {
            return json_decode($setting->value, true) ?? [];
        }

        // For image type, return full URL if value exists
        if ($setting->type === 'image' && $setting->value) {
            return asset('storage/' . $setting->value);
        }

        return $setting->value;
    }

    /**
     * Get raw value without transformation (for forms).
     */
    public static function getRawValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}
