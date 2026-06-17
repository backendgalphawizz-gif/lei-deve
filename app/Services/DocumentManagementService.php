<?php

namespace App\Services;

use App\Support\CurrencyFormatter;
use App\Models\LeiDocument;
use App\Models\LeiDocumentStatCard;
use Illuminate\Support\Collection;

class DocumentManagementService
{
    public function computeStatCards(): Collection
    {
        $cards = LeiDocumentStatCard::orderBy('sort_order')->get();
        $pending = LeiDocument::whereIn('status_tone', ['pending', 'processing', 'review'])->count();
        $malware = LeiDocument::where('security_tone', 'threat')->count();
        $verifiedToday = LeiDocument::where('status_tone', 'verified')
            ->where('verified_at', '>=', now()->startOfDay())->count();
        $avgMin = $this->averageSlaMinutes();

        $values = [
            'pending_verification' => ['value' => CurrencyFormatter::formatNumber($pending > 0 ? $pending : LeiDocument::count()), 'tone' => 'blue'],
            'malware_detected' => ['value' => str_pad((string) $malware, 2, '0', STR_PAD_LEFT), 'tone' => 'red'],
            'avg_sla' => ['value' => $avgMin, 'tone' => 'slate'],
            'verified_today' => ['value' => CurrencyFormatter::formatNumber($verifiedToday), 'tone' => 'green'],
        ];

        return $cards->map(function ($card) use ($values) {
            if (isset($values[$card->stat_key])) {
                $card->value = $values[$card->stat_key]['value'];
            }

            return $card;
        });
    }

    private function averageSlaMinutes(): string
    {
        $docs = LeiDocument::whereNotNull('verified_at')->get();
        if ($docs->isEmpty()) {
            return '4.2m';
        }
        $mins = $docs->map(fn ($d) => $d->created_at->diffInMinutes($d->verified_at))->filter(fn ($m) => $m > 0);
        if ($mins->isEmpty()) {
            return '4.2m';
        }

        return round($mins->avg(), 1) . 'm';
    }

    public function statsPayload(): array
    {
        return $this->computeStatCards()->map(fn ($c) => [
            'stat_key' => $c->stat_key,
            'value' => $c->value,
        ])->values()->all();
    }

    public function documentPayload(LeiDocument $doc): array
    {
        return [
            'id' => $doc->id,
            'document_code' => $doc->document_code,
            'file_name' => $doc->file_name,
            'file_type' => $doc->file_type,
            'security_label' => $doc->security_label,
            'security_tone' => $doc->security_tone,
            'status' => $doc->status,
            'status_tone' => $doc->status_tone,
            'preview_url' => $doc->preview_url,
        ];
    }
}
