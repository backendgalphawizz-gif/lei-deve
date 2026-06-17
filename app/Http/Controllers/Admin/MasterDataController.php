<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiCountry;
use App\Models\LeiMasterDataSetting;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MasterDataController extends Controller
{
    private array $tabs = [
        'country' => 'Country Master',
        'currency' => 'Currency Master',
        'document' => 'Document Types',
        'ticket' => 'Ticket Categories',
        'workflow' => 'Workflow Master',
        'tax' => 'Tax Master',
    ];

    public function index(Request $request)
    {
        $tab = $request->string('tab', 'country')->toString();
        if (! array_key_exists($tab, $this->tabs)) {
            $tab = 'country';
        }

        $perPage = (int) $request->input('per_page', 15);
        $perPage = in_array($perPage, [5, 10, 15, 25, 50], true) ? $perPage : 15;

        $countries = $tab === 'country'
            ? LeiCountry::orderBy('name')->paginate($perPage)->withQueryString()
            : null;

        $validation = LeiMasterDataSetting::getConfig('country_validation', [
            'kyc_verification' => true,
            'tax_residency_proof' => false,
            'swift_bic_validation' => true,
        ]);

        $dropdown = LeiMasterDataSetting::getConfig('country_dropdown', [
            'display_format' => 'name_iso',
            'sort_order' => 'alpha_asc',
            'allow_custom_entries' => false,
        ]);

        return view('admin.master-data.index', [
            'tabs' => $this->tabs,
            'activeTab' => $tab,
            'countries' => $countries,
            'totalCountries' => LeiCountry::count(),
            'validation' => $validation,
            'dropdown' => $dropdown,
        ]);
    }

    public function storeCountry(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'iso_alpha2' => ['required', 'string', 'size:2', 'unique:lei_countries,iso_alpha2'],
            'region' => ['required', 'string', 'max:64'],
            'status' => ['required', 'in:active,inactive'],
            'dialing_code' => ['required', 'string', 'max:8'],
        ]);

        $validated['iso_alpha2'] = strtoupper($validated['iso_alpha2']);
        LeiCountry::create($validated);

        return response()->json(['ok' => true, 'message' => 'Country added successfully.']);
    }

    public function updateValidation(Request $request)
    {
        $validated = $request->validate([
            'kyc_verification' => ['boolean'],
            'tax_residency_proof' => ['boolean'],
            'swift_bic_validation' => ['boolean'],
        ]);

        LeiMasterDataSetting::setConfig('country_validation', $validated);

        return response()->json(['ok' => true, 'message' => 'Validation mapping updated.']);
    }

    public function updateDropdown(Request $request)
    {
        $validated = $request->validate([
            'display_format' => ['required', 'in:name_iso,name_only,iso_only'],
            'sort_order' => ['required', 'in:alpha_asc,alpha_desc,region'],
            'allow_custom_entries' => ['boolean'],
        ]);

        LeiMasterDataSetting::setConfig('country_dropdown', $validated);

        return response()->json(['ok' => true, 'message' => 'Dropdown settings saved.']);
    }

    public function exportCountries(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Country', 'ISO', 'Region', 'Status', 'Dialing Code']);
            LeiCountry::orderBy('name')->each(function ($c) use ($handle) {
                fputcsv($handle, [$c->name, $c->iso_alpha2, $c->region, $c->status, $c->dialing_code]);
            });
            fclose($handle);
        }, 'country-registry-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
