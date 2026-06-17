<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'system_id',
        'password',
        'role',
        'avatar',
        'tier',
        'is_active',
        'last_login_at',
        'organization_id',
        'admin_role_id',
        'account_status',
        'mfa_status',
        'job_title',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function adminRole()
    {
        return $this->belongsTo(AdminRole::class);
    }

    public function modulePermissions()
    {
        return $this->hasMany(UserModulePermission::class);
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }

        return $initials ?: 'U';
    }

    public function getAvatarHueAttribute(): int
    {
        return abs(crc32($this->email ?? $this->name)) % 360;
    }

    public function getAvatarColorAttribute(): string
    {
        $palette = [
            '#4a6fa5',
            '#3d6e8a',
            '#5b6b8a',
            '#4a7c6e',
            '#6b5b8a',
            '#8a5b6b',
            '#5b8a7a',
            '#7a5b6b',
        ];

        $index = abs(crc32($this->email ?? $this->name)) % count($palette);

        return $palette[$index];
    }

    public function profileImageUrl(): string
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }

        $bg = '1a4a7a';
        try {
            $bg = ltrim(LeiBusinessSetting::current()->primary_color ?? '#1a4a7a', '#');
            if (strlen($bg) !== 6) {
                $bg = '1a4a7a';
            }
        } catch (\Throwable) {
            // use default
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name)
            . '&background=' . $bg
            . '&color=fff&size=160&bold=true';
    }
}
