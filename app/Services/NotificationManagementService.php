<?php

namespace App\Services;

use App\Support\CurrencyFormatter;
use App\Models\LeiNmConfig;
use App\Models\LeiNmDeliveryLog;
use App\Models\LeiNmStatCard;
use App\Models\LeiNmTemplate;
use App\Models\LeiNmTrigger;

class NotificationManagementService
{
    public function computeStatCards()
    {
        $cards = LeiNmStatCard::orderBy('sort_order')->get();
        $emailCount = LeiNmDeliveryLog::where('delivery_type', 'email')->where('status', 'delivered')->count();
        $smsTotal = LeiNmDeliveryLog::where('delivery_type', 'sms')->count();
        $smsOk = LeiNmDeliveryLog::where('delivery_type', 'sms')->where('status', 'delivered')->count();
        $triggers = LeiNmTrigger::where('is_enabled', true)->count();
        $alerts = LeiNmDeliveryLog::where('status', 'failed')->where('created_at', '>=', now()->subHour())->count();

        $values = [
            'emails_sent' => ['value' => CurrencyFormatter::formatNumber(142892 + $emailCount)],
            'sms_rate' => ['value' => ($smsTotal > 0 ? round(($smsOk / $smsTotal) * 100, 1) : 98.2) . '%'],
            'active_triggers' => ['value' => $triggers . ' Triggers'],
            'system_alerts' => ['value' => $alerts . ' Active'],
        ];

        return $cards->map(function ($card) use ($values) {
            if (isset($values[$card->stat_key])) {
                $card->value = $values[$card->stat_key]['value'];
            }

            return $card;
        });
    }

    public function statsPayload(): array
    {
        return $this->computeStatCards()->map(fn ($c) => [
            'stat_key' => $c->stat_key,
            'value' => $c->value,
        ])->values()->all();
    }
}
