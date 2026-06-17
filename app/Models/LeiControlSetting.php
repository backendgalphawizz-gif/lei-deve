<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiControlSetting extends Model
{
    protected $fillable = ['setting_key', 'value'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $row = static::where('setting_key', $key)->first();

        return $row?->value ?? $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['setting_key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
        );
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        return (bool) (int) static::getValue($key, $default ? '1' : '0');
    }
}
