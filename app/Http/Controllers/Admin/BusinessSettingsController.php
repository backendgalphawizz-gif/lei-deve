<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiBusinessSetting;
use App\Services\BusinessSettingsService;
use Illuminate\Http\Request;
use App\Rules\PhoneTenDigits;
use Illuminate\Validation\Rule;

class BusinessSettingsController extends Controller
{
    public function __construct(private BusinessSettingsService $business) {}

    public function index()
    {
        $settings = LeiBusinessSetting::current();

        return view('admin.business-settings.index', [
            'settings' => $settings,
            'timezones' => LeiBusinessSetting::timezones(),
            'locales' => LeiBusinessSetting::locales(),
            'dateFormats' => LeiBusinessSetting::dateFormats(),
        ]);
    }

    public function update(Request $request)
    {
        $settings = LeiBusinessSetting::current();

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:150'],
            'legal_name' => ['nullable', 'string', 'max:150'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'portal_title' => ['nullable', 'string', 'max:150'],
            'breadcrumb_root' => ['nullable', 'string', 'max:64'],
            'search_placeholder' => ['nullable', 'string', 'max:120'],
            'welcome_prefix' => ['nullable', 'string', 'max:32'],
            'header_subtitle' => ['nullable', 'string', 'max:150'],
            'header_logo_source' => ['nullable', Rule::in(['sidebar', 'main'])],
            'header_notification_count' => ['nullable', 'integer', 'min:0', 'max:99'],
            'dashboard_title' => ['nullable', 'string', 'max:120'],
            'dashboard_subtitle' => ['nullable', 'string', 'max:255'],
            'dashboard_period_label' => ['nullable', 'string', 'max:64'],
            'registry_authority' => ['nullable', 'string', 'max:150'],
            'cin' => ['nullable', 'string', 'max:40'],
            'gstin' => ['nullable', 'string', 'max:40'],
            'registrar_lei_number' => ['nullable', 'string', 'max:30'],
            'ubisecure_lei' => ['nullable', 'string', 'max:30'],
            'nasdaq_lei' => ['nullable', 'string', 'max:30'],
            'registered_office_address' => ['nullable', 'string', 'max:500'],
            'office_location_address' => ['nullable', 'string', 'max:500'],
            'lou_prefix' => ['required', 'string', 'min:4', 'max:4', 'regex:/^[A-Z0-9]{4}$/i'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sidebar_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'support_email' => ['nullable', 'email', 'max:150'],
            'support_phone' => ['nullable', new PhoneTenDigits],
            'address_line' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:80'],
            'state' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'max:80'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'copyright_text' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', Rule::in(array_keys(LeiBusinessSetting::timezones()))],
            'locale' => ['required', Rule::in(array_keys(LeiBusinessSetting::locales()))],
            'date_format' => ['required', Rule::in(array_keys(LeiBusinessSetting::dateFormats()))],
            'currency_code' => ['required', 'string', 'max:8'],
            'currency_symbol' => ['required', 'string', 'max:8'],
            'renewal_window_days' => ['required', 'integer', 'min:0', 'max:365'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'maintenance_message' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,ico', 'max:512'],
            'sidebar_icon' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,svg', 'max:1024'],
        ]);

        $validated['lou_prefix'] = strtoupper($validated['lou_prefix'] ?? '5493');
        $validated['show_maintenance_banner'] = $request->boolean('show_maintenance_banner');
        $validated['header_show_logo'] = $request->boolean('header_show_logo');
        $validated['header_show_notifications'] = $request->boolean('header_show_notifications');
        $validated['welcome_prefix'] = trim($validated['welcome_prefix'] ?? 'Welcome,') ?: 'Welcome,';
        $validated['header_logo_source'] = $validated['header_logo_source'] ?? 'sidebar';
        unset($validated['logo'], $validated['favicon'], $validated['sidebar_icon']);

        if ($request->boolean('remove_logo')) {
            $this->business->removeAsset($settings, 'logo_path');
        } elseif ($request->hasFile('logo')) {
            $validated['logo_path'] = $this->business->handleUpload($request->file('logo'), 'logo_path', $settings);
        }

        if ($request->boolean('remove_favicon')) {
            $this->business->removeAsset($settings, 'favicon_path');
        } elseif ($request->hasFile('favicon')) {
            $validated['favicon_path'] = $this->business->handleUpload($request->file('favicon'), 'favicon_path', $settings);
        }

        if ($request->boolean('remove_sidebar_icon')) {
            $this->business->removeAsset($settings, 'sidebar_icon_path');
        } elseif ($request->hasFile('sidebar_icon')) {
            $validated['sidebar_icon_path'] = $this->business->handleUpload($request->file('sidebar_icon'), 'sidebar_icon_path', $settings);
        }

        $settings->update($validated);

        return redirect()
            ->route('admin.business-settings.index')
            ->with('success', 'Business settings saved successfully.');
    }
}
