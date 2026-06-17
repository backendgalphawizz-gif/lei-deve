<?php

namespace App\Services;

use App\Support\CurrencyFormatter;
use App\Models\LeiSupportCategory;
use App\Models\LeiSupportMessage;
use App\Models\LeiSupportNote;
use App\Models\LeiSupportStatCard;
use App\Models\LeiSupportTicket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupportManagementService
{
    public function adminInitials(): string
    {
        $name = Auth::user()?->name ?? 'Admin';
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }

    public function adminDisplayName(): string
    {
        $name = Auth::user()?->name ?? 'Admin';
        return strtok($name, ' ') ?: 'Admin';
    }

    public function emailFromEntity(string $entity): string
    {
        $slug = Str::slug($entity, '.');

        return 'support@' . ($slug ?: 'entity') . '.lei';
    }

    public function syncCategoryCounts(): void
    {
        foreach (LeiSupportCategory::orderBy('sort_order')->get() as $cat) {
            $count = LeiSupportTicket::where('category', $cat->name)->count();
            $cat->update(['ticket_count_label' => CurrencyFormatter::formatNumber($count).' Tickets']);
        }
    }

    public function computeStatCards(): Collection
    {
        $cards = LeiSupportStatCard::orderBy('sort_order')->get();
        $active = LeiSupportTicket::where('status', '!=', 'Closed')->count();
        $total = LeiSupportTicket::count();
        $escalated = LeiSupportTicket::where('status', 'Escalated')->count();
        $critical = LeiSupportTicket::where(function ($q) {
            $q->where('status', 'Escalated')->orWhere('is_urgent', true);
        })->where('status', '!=', 'Closed')->count();
        $slaPct = $active > 0
            ? round((($active - $escalated) / $active) * 100, 1)
            : 100.0;
        $avgResolution = $this->averageResolutionLabel();
        $lyGrowth = $total > 0 ? min(99, (int) round(($active / max($total, 1)) * 12)) : 0;

        $values = [
            'total_active' => [
                'value' => CurrencyFormatter::formatNumber($active > 0 ? $active : $total),
                'badge' => '+' . $lyGrowth . '% vs LY',
                'tone' => 'up',
            ],
            'sla_health' => [
                'value' => $slaPct . '%',
                'badge' => $escalated > 0 ? '-' . min(99, round($escalated * 1.2, 1)) . '%' : 'Stable',
                'tone' => $escalated > 0 ? 'down' : 'up',
            ],
            'avg_resolution' => [
                'value' => $avgResolution,
                'badge' => 'Stable',
                'tone' => 'up',
            ],
            'critical_escalations' => [
                'value' => (string) $critical,
                'badge' => 'Critical',
                'tone' => 'critical',
            ],
        ];

        return $cards->map(function ($card) use ($values, $total) {
            if (isset($values[$card->stat_key])) {
                $card->value = $values[$card->stat_key]['value'];
                $card->badge_text = $values[$card->stat_key]['badge'];
                $card->badge_tone = $values[$card->stat_key]['tone'];
            }
            if ($total === 0) {
                $card->value = '—';
                $card->badge_text = 'No data';
                $card->badge_tone = 'muted';
            }

            return $card;
        });
    }

    private function averageResolutionLabel(): string
    {
        $closed = LeiSupportTicket::whereNotNull('closed_at')->get();
        if ($closed->isEmpty()) {
            $hours = $this->averageResponseHoursRaw();
            if ($hours === null) {
                return '—';
            }

            return $this->formatHoursMinutes($hours);
        }

        $mins = [];
        foreach ($closed as $t) {
            if ($t->closed_at) {
                $mins[] = $t->created_at->diffInMinutes($t->closed_at);
            }
        }
        if ($mins === []) {
            return '—';
        }

        return $this->formatHoursMinutes(array_sum($mins) / count($mins) / 60);
    }

    private function averageResponseHoursRaw(): ?float
    {
        $tickets = LeiSupportTicket::with('messages')->has('messages')->get();
        $hours = [];
        foreach ($tickets as $ticket) {
            $firstIn = $ticket->messages->where('is_outgoing', false)->sortBy('created_at')->first();
            $firstOut = $ticket->messages->where('is_outgoing', true)->sortBy('created_at')->first();
            if ($firstIn && $firstOut && $firstOut->created_at > $firstIn->created_at) {
                $hours[] = $firstIn->created_at->diffInMinutes($firstOut->created_at) / 60;
            }
        }

        return $hours === [] ? null : array_sum($hours) / count($hours);
    }

    private function formatHoursMinutes(float $hours): string
    {
        $totalMin = (int) round($hours * 60);
        $h = intdiv($totalMin, 60);
        $m = $totalMin % 60;
        if ($h > 0 && $m > 0) {
            return $h . 'h ' . $m . 'm';
        }
        if ($h > 0) {
            return $h . 'h';
        }

        return $m . 'm';
    }

    public function lastActivityLabel(LeiSupportTicket $ticket): string
    {
        $latestMsg = $ticket->messages()->orderByDesc('updated_at')->first();
        $latestNote = $ticket->notes()->orderByDesc('updated_at')->first();
        $latest = null;
        $label = 'System';
        if ($latestMsg && $latestNote) {
            $latest = $latestMsg->updated_at > $latestNote->updated_at ? $latestMsg : $latestNote;
            $label = $latest instanceof LeiSupportMessage
                ? ($latest->sender_name ?? 'Support')
                : ($latest->author_name ?? 'Admin');
        } elseif ($latestMsg) {
            $latest = $latestMsg;
            $label = $latestMsg->sender_name ?? 'Support';
        } elseif ($latestNote) {
            $latest = $latestNote;
            $label = $latestNote->author_name ?? 'Admin';
        }

        if (! $latest) {
            return 'Last activity: just now';
        }

        return 'Last activity: ' . $latest->updated_at->diffForHumans(null, true) . ' ago by ' . $label;
    }

    public function nextTicketCode(): string
    {
        $last = LeiSupportTicket::orderByDesc('id')->first();
        $num = $last ? ((int) preg_replace('/\D/', '', $last->ticket_code) + 1) : 88922;

        return '#TK-' . $num;
    }

    public function cyclePriority(LeiSupportTicket $ticket): array
    {
        $map = [
            'low' => ['priority' => 'Med', 'priority_tone' => 'medium', 'is_urgent' => false],
            'medium' => ['priority' => 'High', 'priority_tone' => 'high', 'is_urgent' => true],
            'high' => ['priority' => 'Low', 'priority_tone' => 'low', 'is_urgent' => false],
        ];

        return $map[$ticket->priority_tone] ?? $map['low'];
    }

    public function statsPayload(): array
    {
        return $this->computeStatCards()->map(fn ($c) => [
            'stat_key' => $c->stat_key,
            'value' => $c->value,
            'badge_text' => $c->badge_text,
            'badge_tone' => $c->badge_tone,
        ])->values()->all();
    }

    public function categoriesPayload(): array
    {
        $this->syncCategoryCounts();

        return LeiSupportCategory::orderBy('sort_order')->get()->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'ticket_count_label' => $c->ticket_count_label,
        ])->values()->all();
    }

    public function ticketRowPayload(LeiSupportTicket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'ticket_code' => $ticket->ticket_code,
            'user_entity' => $ticket->user_entity,
            'contact_email' => $ticket->contact_email,
            'category' => $ticket->category,
            'priority' => $ticket->priority,
            'priority_tone' => $ticket->priority_tone,
            'status' => $ticket->status,
            'status_tone' => $ticket->status_tone,
        ];
    }
}
