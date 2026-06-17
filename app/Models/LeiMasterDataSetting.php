<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiMasterDataSetting extends Model
{
    protected $fillable = ['setting_key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    public static function getConfig(string $key, array $default = []): array
    {
        $row = static::where('setting_key', $key)->first();

        return $row?->value ?? $default;
    }

    public static function setConfig(string $key, array $value): void
    {
        static::updateOrCreate(['setting_key' => $key], ['value' => $value]);
    }
}
