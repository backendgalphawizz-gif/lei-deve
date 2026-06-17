<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LeiBusinessSetting extends Model
{
    protected $table = 'lei_business_settings';

    protected $fillable = [
        'company_name',
        'legal_name',
        'tagline',
        'portal_title',
        'breadcrumb_root',
        'search_placeholder',
        'welcome_prefix',
        'header_subtitle',
        'header_show_logo',
        'header_logo_source',
        'header_show_notifications',
        'header_notification_count',
        'dashboard_title',
        'dashboard_subtitle',
        'dashboard_period_label',
        'registry_authority',
        'logo_path',
        'favicon_path',
        'sidebar_icon_path',
        'primary_color',
        'accent_color',
        'sidebar_color',
        'support_email',
        'support_phone',
        'address_line',
        'city',
        'state',
        'country',
        'postal_code',
        'website_url',
        'linkedin_url',
        'twitter_url',
        'copyright_text',
        'timezone',
        'locale',
        'date_format',
        'currency_code',
        'currency_symbol',
        'meta_description',
        'show_maintenance_banner',
        'maintenance_message',
    ];

    protected $casts = [
        'show_maintenance_banner' => 'boolean',
        'header_show_logo' => 'boolean',
        'header_show_notifications' => 'boolean',
        'header_notification_count' => 'integer',
    ];

    public static function defaults(): array
    {
        return [
            'company_name' => 'LEI Registry Services',
            'legal_name' => 'Global LEI Foundation Pvt. Ltd.',
            'tagline' => 'Registry Services',
            'portal_title' => 'Super Admin Portal',
            'breadcrumb_root' => 'Registry',
            'search_placeholder' => 'Global Search...',
            'welcome_prefix' => 'Welcome,',
            'header_subtitle' => null,
            'header_show_logo' => false,
            'header_logo_source' => 'sidebar',
            'header_show_notifications' => true,
            'header_notification_count' => 1,
            'dashboard_title' => 'System Overview',
            'dashboard_subtitle' => 'Real-time operational dashboard and registry status.',
            'dashboard_period_label' => 'Last 24 Hours',
            'registry_authority' => 'GLEIF Accredited LOU',
            'primary_color' => '#0b162c',
            'accent_color' => '#c9a227',
            'sidebar_color' => '#000b1d',
            'support_email' => 'support@lei-registry.int',
            'support_phone' => '+91 11 4000 0000',
            'address_line' => 'Level 12, Registry Tower',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'country' => 'India',
            'postal_code' => '110001',
            'website_url' => 'https://lei-registry.int',
            'copyright_text' => '© ' . date('Y') . ' LEI Registry Services. All rights reserved.',
            'timezone' => 'Asia/Kolkata',
            'locale' => 'en_IN',
            'date_format' => 'd/m/Y',
            'currency_code' => 'INR',
            'currency_symbol' => '₹',
            'meta_description' => 'Legal Entity Identifier registry administration portal.',
        ];
    }

    public static function current(): self
    {
        $row = static::query()->first();

        if ($row) {
            return $row;
        }

        return static::create(static::defaults());
    }

    public function assetUrl(?string $path, string $fallback): string
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }

        return asset($fallback);
    }

    public function logoUrl(): string
    {
        return $this->assetUrl($this->logo_path, 'images/lei-logo.png');
    }

    public function faviconUrl(): ?string
    {
        if ($this->favicon_path && Storage::disk('public')->exists($this->favicon_path)) {
            return asset('storage/' . $this->favicon_path);
        }

        return null;
    }

    public function sidebarIconUrl(): string
    {
        return $this->assetUrl($this->sidebar_icon_path, 'images/lei-logo.png');
    }

    public static function timezones(): array
    {
        return [
            'Asia/Kolkata' => 'Asia/Kolkata (IST)',
            'UTC' => 'UTC',
            'Europe/London' => 'Europe/London (GMT)',
            'America/New_York' => 'America/New_York (EST)',
            'Asia/Singapore' => 'Asia/Singapore',
        ];
    }

    public static function locales(): array
    {
        return [
            'en_IN' => 'English (India)',
            'hi' => 'Hindi',
            'en' => 'English',
            'fr' => 'French',
            'de' => 'German',
        ];
    }

    public static function dateFormats(): array
    {
        return [
            'd/m/Y' => '05/06/2026',
            'd-m-Y' => '05-06-2026',
            'M j, Y' => 'Jun 5, 2026',
            'Y-m-d' => '2026-06-05',
            'F j, Y' => 'June 5, 2026',
        ];
    }

    public function numberLocale(): string
    {
        return match ($this->locale) {
            'hi' => 'hi_IN',
            'en', 'en_IN', null, '' => 'en_IN',
            default => str_contains((string) $this->locale, '_') ? (string) $this->locale : 'en_IN',
        };
    }

    public function formatDate(?\Illuminate\Support\Carbon $date, ?string $pattern = null): string
    {
        if (! $date) {
            return '—';
        }

        $tz = $this->timezone ?: 'Asia/Kolkata';

        return $date->timezone($tz)->format($pattern ?? $this->date_format ?? 'd/m/Y');
    }

    public function welcomeName(): string
    {
        $user = auth()->user();
        if (! $user) {
            return 'Admin';
        }

        return $user->isSuperAdmin() ? 'Admin' : strtok($user->name, ' ');
    }

    public function sidebarBrandTitle(): string
    {
        $user = auth()->user();

        if ($user?->relationLoaded('adminRole') || $user?->admin_role_id) {
            $user->loadMissing('adminRole');
        }

        if ($user?->adminRole?->name) {
            return $user->adminRole->name;
        }

        if ($user?->isSuperAdmin()) {
            return 'Super Admin';
        }

        $title = $this->portal_title ?? 'Super Admin';

        return preg_replace('/\s+Portal$/i', '', $title) ?: $title;
    }

    public function headerSubtitleText(): string
    {
        return $this->header_subtitle
            ?: $this->registry_authority
            ?: $this->tagline
            ?: '';
    }

    public function headerLogoUrl(): string
    {
        return match ($this->header_logo_source) {
            'main' => $this->logoUrl(),
            default => $this->sidebarIconUrl(),
        };
    }

    public function avatarUrl(): string
    {
        $name = urlencode(auth()->user()?->name ?? 'Admin');

        return 'https://ui-avatars.com/api/?name=' . $name
            . '&background=' . $this->avatarBackground()
            . '&color=fff&size=80&bold=true';
    }

    public function avatarBackground(): string
    {
        $hex = ltrim($this->primary_color ?? '#1a4a7a', '#');

        return strlen($hex) === 6 ? $hex : '1a4a7a';
    }

    public function cssVars(): string
    {
        $primary = $this->primary_color ?? '#0f3057';
        $accent = $this->accent_color ?? '#b8956a';
        $sidebar = $this->sidebar_color ?? '#000b1d';

        return implode(';', [
            '--lei-sidebar-bg:' . $sidebar,
            '--lei-navy:' . $primary,
            '--lei-navy-dark:' . $primary,
            '--lei-navy-deep:' . $sidebar,
            '--lei-blue:' . $primary,
            '--lei-gold:' . $accent,
            '--lei-gold-hover:' . $accent,
            '--lei-header-accent:' . $primary,
        ]);
    }
}
