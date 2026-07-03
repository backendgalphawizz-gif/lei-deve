<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GleifApiService
{
    private const BASE_URL = 'https://api.gleif.org/api/v1';

    public function isEnabled(): bool
    {
        return (bool) config('services.gleif.enabled', true);
    }

    /**
     * @return array{records: array<int, array<string, mixed>>, total: int}
     */
    public function search(string $query, string $type = 'all', int $page = 1, int $perPage = 15): array
    {
        if (! $this->isEnabled() || trim($query) === '') {
            return ['records' => [], 'total' => 0];
        }

        $filters = $this->filtersForType($query, $type);
        if ($filters === []) {
            return ['records' => [], 'total' => 0];
        }

        $params = [
            'page[number]' => max(1, $page),
            'page[size]' => min(50, max(1, $perPage)),
        ];

        foreach ($filters as $key => $value) {
            $params["filter[{$key}]"] = $value;
        }

        $cacheKey = 'gleif.search.'.md5(json_encode([$params, $type, $query]));

        $payload = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($params) {
            return $this->get('/lei-records', $params);
        });

        if ($payload === null) {
            return ['records' => [], 'total' => 0];
        }

        $records = collect($payload['data'] ?? [])
            ->map(fn (array $item) => $this->normalizeRecord($item))
            ->filter()
            ->values()
            ->all();

        return [
            'records' => $records,
            'total' => (int) ($payload['meta']['pagination']['total'] ?? count($records)),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function suggest(string $query, string $type = 'all', int $limit = 8): array
    {
        if (! $this->isEnabled() || mb_strlen(trim($query)) < 2) {
            return [];
        }

        $result = $this->search($query, $type, 1, $limit);

        return $result['records'];
    }

    public function findByLei(string $leiNumber): ?array
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $normalized = $this->normalizeLei($leiNumber);
        if (strlen($normalized) !== 20) {
            return null;
        }

        $cacheKey = 'gleif.lei.'.$normalized;

        $payload = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($normalized) {
            return $this->get('/lei-records/'.$normalized);
        });

        if ($payload === null || empty($payload['data'])) {
            return null;
        }

        $data = array_is_list($payload['data']) ? ($payload['data'][0] ?? null) : $payload['data'];

        return $data ? $this->normalizeRecord($data) : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function normalizeRecord(array $item): ?array
    {
        $attrs = $item['attributes'] ?? [];
        $entity = $attrs['entity'] ?? [];
        $registration = $attrs['registration'] ?? [];
        $lei = $this->normalizeLei($item['id'] ?? ($attrs['lei'] ?? ''));

        if ($lei === '') {
            return null;
        }

        $legalName = $entity['legalName']['name'] ?? 'Unknown entity';
        $country = $entity['legalAddress']['country'] ?? ($entity['jurisdiction'] ?? '—');
        $regNo = $entity['registeredAs'] ?? null;
        $nextRenewal = ! empty($registration['nextRenewalDate'])
            ? Carbon::parse($registration['nextRenewalDate'])
            : null;

        $meta = $this->gleifStatusMeta(
            $registration['status'] ?? null,
            $entity['status'] ?? null,
            $nextRenewal,
        );

        $addressLines = $entity['legalAddress']['addressLines'] ?? [];
        $city = $entity['legalAddress']['city'] ?? null;
        $address = collect($addressLines)->filter()->implode(', ');
        if ($city) {
            $address = trim($address.($address ? ', ' : '').$city);
        }

        return [
            'source' => 'gleif',
            'source_label' => 'GLEIF Global Index',
            'lei_number' => $lei,
            'entity_name' => $legalName,
            'registration_number' => $regNo,
            'country' => $country,
            'expiry_label' => $nextRenewal?->format('M j, Y'),
            'expiry_date' => $nextRenewal,
            'entity_type' => $entity['legalForm']['id'] ?? null,
            'registered_address' => $address ?: null,
            'gleif_entity_status' => $entity['status'] ?? null,
            'gleif_registration_status' => $registration['status'] ?? null,
            'managing_lou' => $registration['managingLou'] ?? null,
            'initial_registration_date' => ! empty($registration['initialRegistrationDate'])
                ? Carbon::parse($registration['initialRegistrationDate'])
                : null,
            'url' => route('registry.show', $lei),
            'gleif_url' => 'https://search.gleif.org/#/record/'.$lei,
        ] + $meta;
    }

    /**
     * @return array{status: string, status_label: string, status_tone: string, can_renew: bool, renew_url: ?string}
     */
    public function gleifStatusMeta(?string $registrationStatus, ?string $entityStatus, ?Carbon $nextRenewal): array
    {
        $registrationStatus = strtoupper((string) $registrationStatus);
        $entityStatus = strtoupper((string) $entityStatus);

        if ($registrationStatus === 'LAPSED' || $registrationStatus === 'RETIRED' || $entityStatus === 'INACTIVE') {
            return [
                'status' => 'expired',
                'status_label' => $registrationStatus === 'RETIRED' ? 'RETIRED' : 'LAPSED',
                'status_tone' => 'expired',
                'can_renew' => false,
                'renew_url' => null,
            ];
        }

        if ($nextRenewal && $nextRenewal->isPast()) {
            return [
                'status' => 'expired',
                'status_label' => 'RENEWAL OVERDUE',
                'status_tone' => 'expired',
                'can_renew' => false,
                'renew_url' => null,
            ];
        }

        if ($nextRenewal && now()->diffInDays($nextRenewal, false) <= 90) {
            return [
                'status' => 'expiring',
                'status_label' => 'RENEWAL DUE',
                'status_tone' => 'warning',
                'can_renew' => false,
                'renew_url' => null,
            ];
        }

        return [
            'status' => 'active',
            'status_label' => 'ISSUED',
            'status_tone' => 'active',
            'can_renew' => false,
            'renew_url' => null,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function filtersForType(string $query, string $type): array
    {
        $q = trim($query);
        $normalizedLei = $this->normalizeLei($q);

        return match ($type) {
            'lei' => strlen($normalizedLei) === 20
                ? ['lei' => $normalizedLei]
                : (strlen($normalizedLei) >= 4 ? ['lei' => $normalizedLei] : []),
            'company' => ['entity.legalName' => $q],
            'registration' => ['entity.registeredAs' => $q],
            default => $this->filtersForAll($q, $normalizedLei),
        };
    }

    /**
     * @return array<string, string>
     */
    private function filtersForAll(string $query, string $normalizedLei): array
    {
        if (strlen($normalizedLei) === 20) {
            return ['lei' => $normalizedLei];
        }

        if (preg_match('/^[A-Z0-9]{8,20}$/', $normalizedLei)) {
            return ['lei' => $normalizedLei];
        }

        if (preg_match('/^[A-Z0-9]{6,}$/i', $query) && ! str_contains($query, ' ')) {
            return ['entity.registeredAs' => $query];
        }

        return ['entity.legalName' => $query];
    }

    private function normalizeLei(string $lei): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($lei)) ?? trim($lei));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function get(string $path, array $query = []): ?array
    {
        try {
            $response = Http::timeout(8)
                ->accept('application/vnd.api+json')
                ->get(self::BASE_URL.$path, $query);

            if ($response->status() === 404) {
                return null;
            }

            if (! $response->successful()) {
                return null;
            }

            return $response->json();
        } catch (\Throwable) {
            return null;
        }
    }
}
