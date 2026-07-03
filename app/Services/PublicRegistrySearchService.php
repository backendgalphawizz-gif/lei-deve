<?php

namespace App\Services;

use App\Models\LeiApplication;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class PublicRegistrySearchService
{
    public const TYPES = ['all', 'lei', 'company', 'registration'];

    private const GLEIF_SEARCH_CAP = 200;

    public function __construct(
        private SubscriptionService $subscriptions,
        private GleifApiService $gleif,
    ) {}

    public function search(string $query, string $type = 'all', int $perPage = 15): LengthAwarePaginator
    {
        $q = trim($query);
        $type = in_array($type, self::TYPES, true) ? $type : 'all';
        $page = max(1, (int) request()->query('page', 1));

        $merged = $this->mergedResults($q, $type);
        $items = $merged->slice(($page - 1) * $perPage, $perPage)->values();

        return new Paginator(
            $items,
            $merged->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()],
        );
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function mergedResults(string $q, string $type): Collection
    {
        $local = $this->publishedQuery($type, $q)
            ->get()
            ->map(fn (LeiApplication $app) => $this->normalizeLocalRecord($app));

        $localLeis = $local->pluck('lei_number')
            ->map(fn ($lei) => strtoupper((string) $lei))
            ->all();

        $gleifBatch = $this->gleif->search($q, $type, 1, self::GLEIF_SEARCH_CAP);
        $gleif = collect($gleifBatch['records'])
            ->reject(fn (array $record) => in_array(strtoupper($record['lei_number']), $localLeis, true))
            ->values();

        return $local->concat($gleif)->values();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function suggest(string $query, string $type = 'all', int $limit = 8): array
    {
        $q = trim($query);
        if (mb_strlen($q) < 2) {
            return [];
        }

        $type = in_array($type, self::TYPES, true) ? $type : 'all';
        $half = (int) ceil($limit / 2);

        $local = $this->publishedQuery($type, $q)
            ->limit($half)
            ->get()
            ->map(fn (LeiApplication $app) => $this->normalizeLocalRecord($app));

        $localLeis = $local->pluck('lei_number')->map(fn ($lei) => strtoupper($lei))->all();

        $gleif = collect($this->gleif->suggest($q, $type, $limit))
            ->reject(fn (array $record) => in_array(strtoupper($record['lei_number']), $localLeis, true))
            ->take($limit - $local->count());

        return $local->concat($gleif)->take($limit)->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizeLocalRecord(LeiApplication $application): array
    {
        $meta = $this->recordMeta($application);

        return [
            'source' => 'local',
            'source_label' => 'Our Registry',
            'lei_number' => $application->lei_number,
            'entity_name' => $application->entity_name,
            'registration_number' => $this->registrationNumber($application),
            'country' => $application->country,
            'expiry_label' => $application->expiry_date?->format('M j, Y'),
            'expiry_date' => $application->expiry_date,
            'entity_type' => $this->entityType($application),
            'registered_address' => $this->registeredAddress($application),
            'url' => route('registry.show', $application->lei_number),
            'application' => $application,
        ] + $meta;
    }

    /**
     * @return array{status: string, status_label: string, status_tone: string, can_renew: bool, renew_url: ?string, expiry_label: ?string}
     */
    public function recordMeta(LeiApplication $application): array
    {
        $status = $this->leiStatus($application);
        $canRenew = $this->subscriptions->isEntityEligibleForRenewal($application);

        return [
            'status' => $status,
            'status_label' => match ($status) {
                'expired' => 'EXPIRED',
                'expiring' => 'RENEWAL DUE',
                default => 'ISSUED',
            },
            'status_tone' => match ($status) {
                'expired' => 'expired',
                'expiring' => 'warning',
                default => 'active',
            },
            'can_renew' => $canRenew,
            'renew_url' => $canRenew ? $this->renewUrl($application) : null,
            'expiry_label' => $application->expiry_date?->format('M j, Y'),
        ];
    }

    public function leiStatus(LeiApplication $application): string
    {
        if (! $application->expiry_date) {
            return 'active';
        }

        if ($application->expiry_date->isPast()) {
            return 'expired';
        }

        if ($this->subscriptions->isEntityEligibleForRenewal($application)) {
            return 'expiring';
        }

        return 'active';
    }

    public function renewUrl(LeiApplication $application): string
    {
        return route('pricing', ['lei' => $application->lei_number]).'#renewal';
    }

    public function registerUrl(): string
    {
        return route('register');
    }

    /**
     * @return array{record: array<string, mixed>, source: string}|null
     */
    public function resolveRecord(string $leiNumber): ?array
    {
        $application = $this->findByLei($leiNumber);
        if ($application) {
            $application->loadMissing('certificate');

            return [
                'source' => 'local',
                'record' => $this->normalizeLocalRecord($application),
                'application' => $application,
            ];
        }

        $gleif = $this->gleif->findByLei($leiNumber);
        if ($gleif) {
            return [
                'source' => 'gleif',
                'record' => $gleif,
                'application' => null,
            ];
        }

        return null;
    }

    private function publishedQuery(string $type, string $q): Builder
    {
        $builder = LeiApplication::query()
            ->where('status', 'approved')
            ->whereNotNull('lei_number')
            ->where('lei_number', '!=', '')
            ->orderByDesc('submitted_on')
            ->orderByDesc('updated_at');

        if ($q !== '') {
            $builder->where(function (Builder $w) use ($q, $type) {
                if ($type === 'all' || $type === 'lei') {
                    $normalized = strtoupper(preg_replace('/\s+/', '', $q) ?? $q);
                    $w->orWhere('lei_number', 'like', '%'.$normalized.'%');
                }
                if ($type === 'all' || $type === 'company') {
                    $w->orWhere('entity_name', 'like', '%'.$q.'%');
                }
                if ($type === 'all' || $type === 'registration') {
                    $w->orWhere('draft_data->registration_number', 'like', '%'.$q.'%');
                }
            });
        }

        return $builder;
    }

    public function findByLei(string $leiNumber): ?LeiApplication
    {
        $normalized = strtoupper(preg_replace('/\s+/', '', trim($leiNumber)) ?? trim($leiNumber));

        if ($normalized === '') {
            return null;
        }

        return LeiApplication::query()
            ->where('status', 'approved')
            ->where('lei_number', $normalized)
            ->first();
    }

    public function registrationNumber(LeiApplication $application): ?string
    {
        $draft = $application->draft_data ?? [];

        return $draft['registration_number'] ?? null;
    }

    public function entityType(LeiApplication $application): ?string
    {
        $draft = $application->draft_data ?? [];

        return $draft['entity_type'] ?? null;
    }

    public function registeredAddress(LeiApplication $application): ?string
    {
        $draft = $application->draft_data ?? [];

        return $draft['registered_address'] ?? null;
    }
}
