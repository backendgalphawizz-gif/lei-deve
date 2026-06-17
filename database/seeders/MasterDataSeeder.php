<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\LeiCountry;
use App\Models\LeiMasterDataSetting;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        AdminMenuItem::where('label', 'Master Data')->update([
            'route_name' => 'admin.master-data.index',
        ]);

        $featured = [
            ['United States', 'US', 'Americas', 'active', '+1'],
            ['United Kingdom', 'GB', 'Europe', 'active', '+44'],
            ['Singapore', 'SG', 'Asia-Pacific', 'inactive', '+65'],
            ['United Arab Emirates', 'AE', 'Middle East', 'active', '+971'],
            ['Switzerland', 'CH', 'Europe', 'active', '+41'],
        ];

        foreach ($featured as [$name, $iso, $region, $status, $dial]) {
            LeiCountry::updateOrCreate(
                ['iso_alpha2' => $iso],
                ['name' => $name, 'region' => $region, 'status' => $status, 'dialing_code' => $dial]
            );
        }

        $bulk = [
                ['Germany', 'DE', 'Europe', 'active', '+49'],
                ['France', 'FR', 'Europe', 'active', '+33'],
                ['Japan', 'JP', 'Asia-Pacific', 'active', '+81'],
                ['Canada', 'CA', 'Americas', 'active', '+1'],
                ['Australia', 'AU', 'Asia-Pacific', 'active', '+61'],
                ['India', 'IN', 'Asia-Pacific', 'active', '+91'],
                ['Brazil', 'BR', 'Americas', 'active', '+55'],
                ['Netherlands', 'NL', 'Europe', 'active', '+31'],
                ['Sweden', 'SE', 'Europe', 'active', '+46'],
                ['Norway', 'NO', 'Europe', 'active', '+47'],
            ];
        foreach ($bulk as $row) {
            LeiCountry::updateOrCreate(
                ['iso_alpha2' => $row[1]],
                ['name' => $row[0], 'region' => $row[2], 'status' => $row[3], 'dialing_code' => $row[4]]
            );
        }

        $regions = ['Europe', 'Americas', 'Asia-Pacific', 'Middle East', 'Africa'];
        $n = 0;
        while (LeiCountry::count() < 195 && $n < 500) {
            $c1 = chr(65 + ($n % 26));
            $c2 = chr(65 + (int) ($n / 26) % 26);
            $iso = $c1.$c2;
            $n++;
            if (LeiCountry::where('iso_alpha2', $iso)->exists()) {
                continue;
            }
            LeiCountry::create([
                'name' => 'Registry Territory '.$n,
                'iso_alpha2' => $iso,
                'region' => $regions[$n % count($regions)],
                'status' => $n % 11 === 0 ? 'inactive' : 'active',
                'dialing_code' => '+'.(200 + ($n % 800)),
            ]);
        }

        LeiMasterDataSetting::updateOrCreate(
            ['setting_key' => 'country_validation'],
            ['value' => [
                'kyc_verification' => true,
                'tax_residency_proof' => false,
                'swift_bic_validation' => true,
            ]]
        );

        LeiMasterDataSetting::updateOrCreate(
            ['setting_key' => 'country_dropdown'],
            ['value' => [
                'display_format' => 'name_iso',
                'sort_order' => 'alpha_asc',
                'allow_custom_entries' => false,
            ]]
        );
    }
}
