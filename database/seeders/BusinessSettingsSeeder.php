<?php

namespace Database\Seeders;

use App\Models\LeiBusinessSetting;
use App\Models\LeiReportsStatCard;
use App\Services\DashboardStatsService;
use Illuminate\Database\Seeder;

class BusinessSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = LeiBusinessSetting::defaults();
        $row = LeiBusinessSetting::query()->first();

        if ($row) {
            if (($row->currency_code ?? '') === 'USD' || ($row->currency_symbol ?? '') === '$') {
                $row->currency_code = 'INR';
                $row->currency_symbol = '₹';
            }

            if (in_array($row->locale, ['en', null, ''], true)) {
                $row->locale = 'en_IN';
            }

            if (empty($row->date_format) || $row->date_format === 'M j, Y') {
                $row->date_format = 'd/m/Y';
            }

            $legacySidebar = ['#0b162c', '#0d1b2a', '#001529', '#0f3057'];
            if (in_array(strtolower((string) $row->sidebar_color), array_map('strtolower', $legacySidebar), true)) {
                $row->sidebar_color = '#000b1d';
            }

            foreach (['breadcrumb_root', 'search_placeholder', 'welcome_prefix', 'header_logo_source', 'dashboard_title', 'dashboard_subtitle', 'dashboard_period_label'] as $key) {
                if (empty($row->{$key})) {
                    $row->{$key} = $defaults[$key];
                }
            }
            $row->save();

            LeiReportsStatCard::query()
                ->where('stat_key', 'financial_yield')
                ->where('value', 'like', '$%')
                ->update(['value' => '₹4.2 Cr']);

            app(DashboardStatsService::class)->syncLiveSnapshots();
        } else {
            LeiBusinessSetting::create($defaults);
        }
    }
}
