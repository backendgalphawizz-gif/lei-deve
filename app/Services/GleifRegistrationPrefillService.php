<?php

namespace App\Services;

use Illuminate\Http\Request;

class GleifRegistrationPrefillService
{
    public const SESSION_KEY = 'registration_prefill';

    public function __construct(private PublicRegistrySearchService $registry) {}

    public function store(array $prefill): void
    {
        session([self::SESSION_KEY => $prefill]);
    }

    public function get(): ?array
    {
        $prefill = session(self::SESSION_KEY);

        return is_array($prefill) ? $prefill : null;
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function fromLei(string $leiNumber): ?array
    {
        $resolved = $this->registry->resolveRecord($leiNumber);
        if (! $resolved) {
            return null;
        }

        return $this->fromRecord($resolved['record'], $resolved['source']);
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, mixed>
     */
    public function fromRecord(array $record, string $source = 'gleif'): array
    {
        return [
            'source' => $source,
            'source_lei' => $record['lei_number'] ?? null,
            'entity_name' => (string) ($record['entity_name'] ?? ''),
            'registration_number' => (string) ($record['registration_number'] ?? ''),
            'country' => $this->resolveCountryName((string) ($record['country'] ?? '')),
            'registered_address' => (string) ($record['registered_address'] ?? ''),
        ];
    }

    public function fromRequest(Request $request): ?array
    {
        $lei = $request->string('lei')->trim()->toString();
        if ($lei !== '') {
            return $this->fromLei($lei);
        }

        $entityName = $request->string('entity_name')->trim()->toString();
        if ($entityName === '') {
            return null;
        }

        return [
            'source' => 'search',
            'source_lei' => null,
            'entity_name' => $entityName,
            'registration_number' => $request->string('registration_number')->trim()->toString(),
            'country' => $this->resolveCountryName($request->string('country')->trim()->toString()),
            'registered_address' => $request->string('registered_address')->trim()->toString(),
        ];
    }

    public function registerWithUsUrl(?string $leiNumber = null): string
    {
        if ($leiNumber) {
            return route('registry.register-with-us', ['lei' => $leiNumber]);
        }

        return route('register');
    }

    public function resolveCountryName(string $country): string
    {
        $country = trim($country);
        if ($country === '') {
            return '';
        }

        if (strlen($country) > 3) {
            return $country;
        }

        $map = [
            'IN' => 'India',
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'UK' => 'United Kingdom',
            'DE' => 'Germany',
            'SG' => 'Singapore',
            'FR' => 'France',
            'NL' => 'Netherlands',
            'AU' => 'Australia',
            'CA' => 'Canada',
            'JP' => 'Japan',
            'CN' => 'China',
            'AE' => 'United Arab Emirates',
            'CH' => 'Switzerland',
            'IE' => 'Ireland',
            'LU' => 'Luxembourg',
            'HK' => 'Hong Kong',
        ];

        return $map[strtoupper($country)] ?? $country;
    }
}
