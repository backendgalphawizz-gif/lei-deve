<?php

namespace App\Services;

use App\Models\LeiBusinessSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BusinessSettingsService
{
    private const UPLOAD_DIR = 'business';

    public function handleUpload(?UploadedFile $file, string $field, LeiBusinessSetting $settings): ?string
    {
        if (! $file) {
            return $settings->{$field};
        }

        $old = $settings->{$field};
        if ($old) {
            Storage::disk('public')->delete($old);
        }

        $ext = $file->getClientOriginalExtension() ?: $file->extension() ?: 'png';
        $name = $field . '-' . Str::random(8) . '.' . strtolower($ext);

        return $file->storeAs(self::UPLOAD_DIR, $name, 'public');
    }

    public function removeAsset(LeiBusinessSetting $settings, string $field): void
    {
        $path = $settings->{$field};
        if ($path) {
            Storage::disk('public')->delete($path);
        }
        $settings->{$field} = null;
    }
}
