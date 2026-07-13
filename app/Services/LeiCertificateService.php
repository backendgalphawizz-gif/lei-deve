<?php

namespace App\Services;

use App\Models\LeiApplication;
use App\Models\LeiBusinessSetting;
use App\Models\LeiCertificate;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LeiCertificateService
{
    public function __construct(private LeiCodeGenerator $leiCodes) {}

    /**
     * Generate unsigned ISO 17442-2 certificate after admin approval.
     */
    public function generateUnsigned(LeiApplication $application): LeiCertificate
    {
        $application->loadMissing(['user', 'subscription']);

        $settings = LeiBusinessSetting::current();
        $leiNumber = $application->lei_number ?: $application->user?->lei_number;

        if (! $leiNumber) {
            $leiNumber = $this->leiCodes->generate();
            $application->update(['lei_number' => $leiNumber]);
        }

        $years = (int) ($application->subscription?->duration_years ?: 1);
        $validFrom = now();
        $validUntil = $application->expiry_date
            ? $application->expiry_date->copy()->startOfDay()
            : now()->addYears($years);

        $draft = $application->draft_data ?? [];
        $role = $draft['authorized_person_role'] ?? $draft['signature_name'] ?? 'Authorized Signatory';
        $country = $this->countryCode($application->country);

        $issuerDn = sprintf(
            'C=%s, O=%s, OU=LEI Registry, CN=%s',
            $country,
            str_replace(',', '', $settings->company_name ?? 'LEI Registry'),
            str_replace(',', '', $settings->registry_authority ?? 'GLEIF Accredited LOU')
        );

        $subjectDn = sprintf(
            'C=%s, L=%s, O=%s, CN=%s',
            $country,
            $this->extractCity($draft['registered_address'] ?? $application->country),
            str_replace(',', '', $application->entity_name),
            str_replace(',', '', $application->entity_name)
        );

        $serial = strtoupper(bin2hex(random_bytes(8)));

        $certificate = LeiCertificate::updateOrCreate(
            ['lei_application_id' => $application->id],
            [
                'status' => 'unsigned',
                'serial_number' => $serial,
                'signature_algorithm' => 'sha256WithRSAEncryption',
                'issuer_dn' => $issuerDn,
                'subject_dn' => $subjectDn,
                'lei_oid' => LeiCertificate::OID_LEI,
                'role_oid' => LeiCertificate::OID_ROLE,
                'certificate_role' => Str::limit($role, 80, ''),
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
            ]
        );

        $pdfPath = $this->renderPdf($certificate, $application, $settings, signed: false);
        $certificate->update(['unsigned_pdf_path' => $pdfPath, 'status' => 'pending_ca']);

        return $certificate->fresh();
    }

    /**
     * CA digitally signs the certificate and generates the signed PDF.
     */
    public function signByCa(LeiCertificate $certificate, User $caUser, ?string $notes = null): LeiCertificate
    {
        $certificate->loadMissing('application.user');
        $application = $certificate->application;
        $settings = LeiBusinessSetting::current();

        $payload = implode('|', [
            $certificate->serial_number,
            $application->lei_number,
            $certificate->subject_dn,
            $certificate->valid_from?->toIso8601String(),
            $certificate->valid_until?->toIso8601String(),
        ]);

        $signatureHash = hash('sha256', $payload);

        try {
            $x509Path = $this->generateX509Pem($certificate, $application, $settings);
        } catch (\Throwable) {
            $x509Path = $this->storeFallbackX509Pem($certificate, $application, $signatureHash);
        }

        $signedPdfPath = $this->renderPdf($certificate, $application, $settings, signed: true, caUser: $caUser, signatureHash: $signatureHash);

        $certificate->update([
            'status' => 'signed',
            'signed_pdf_path' => $signedPdfPath,
            'x509_pem_path' => $x509Path,
            'signature_hash' => $signatureHash,
            'signed_by' => $caUser->id,
            'signed_at' => now(),
            'ca_notes' => $notes,
        ]);

        return $certificate->fresh();
    }

    private function renderPdf(
        LeiCertificate $certificate,
        LeiApplication $application,
        LeiBusinessSetting $settings,
        bool $signed,
        ?User $caUser = null,
        ?string $signatureHash = null,
    ): string {
        $caSignatureDataUri = $signed && $caUser ? $caUser->caSignatureDataUri() : null;

        $pdf = Pdf::loadView('applicant.certificates.lei-official', array_merge(
            $this->certificateViewData($certificate, $application, $settings),
            compact('certificate', 'application', 'settings', 'signed', 'caUser', 'signatureHash', 'caSignatureDataUri'),
        ))
            ->setPaper('A4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => false]);

        $dir = 'certificates/'.$application->id;
        $filename = ($signed ? 'signed-' : 'unsigned-').$certificate->serial_number.'.pdf';
        $path = $dir.'/'.$filename;

        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    /**
     * @return array<string, mixed>
     */
    private function certificateViewData(
        LeiCertificate $certificate,
        LeiApplication $application,
        LeiBusinessSetting $settings,
    ): array {
        $draft = $application->draft_data ?? [];
        $registeredAddress = $this->formatRegisteredAddress(
            $draft['registered_address'] ?? null,
            $application->country,
        );
        $renewalDate = $certificate->valid_until ?? $application->expiry_date;
        $renewalWindowDays = (int) ($settings->renewal_window_days ?: 60);
        $renewalWindowStart = $renewalDate
            ? $renewalDate->copy()->subDays($renewalWindowDays)->format('Y-m-d')
            : now()->format('Y-m-d');

        $verifyUrl = route('registry.show', $application->lei_number);
        $websiteUrl = rtrim((string) ($settings->website_url ?: config('app.url')), '/');
        $renewUrl = route('pricing', ['lei' => $application->lei_number]).'#renewal';
        $countryCode = $this->countryCode($application->country);
        $showIndiaFlag = $countryCode === 'IN';

        return [
            'registeredAddress' => $registeredAddress,
            'renewalDate' => $renewalDate?->format('Y-m-d') ?? '—',
            'renewalWindowStart' => $renewalWindowStart,
            'verifyUrl' => $verifyUrl,
            'websiteUrl' => $websiteUrl,
            'renewUrl' => $renewUrl,
            'qrDataUri' => $this->qrDataUri($verifyUrl),
            'renewQrDataUri' => $this->qrDataUri($renewUrl),
            'sealDataUri' => $this->localImageDataUri(public_path('images/certificates/lei-certificate-seal.png')),
            'flagDataUri' => $showIndiaFlag
                ? $this->localImageDataUri(public_path('images/certificates/india-flag.png'))
                : null,
            'brandName' => $settings->company_name ?: 'LEI Registry',
            'legalName' => $settings->legal_name,
            'registryAuthority' => $settings->registry_authority,
            'supportEmail' => $settings->support_email,
            'supportPhone' => $settings->support_phone,
            'cin' => $settings->cin,
            'gstin' => $settings->gstin,
            'registrarLei' => $settings->registrar_lei_number,
            'ubisecureLei' => $settings->ubisecure_lei,
            'nasdaqLei' => $settings->nasdaq_lei,
            'registeredOfficeAddress' => $settings->registered_office_address,
            'officeLocationAddress' => $settings->office_location_address,
        ];
    }

    private function localImageDataUri(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => null,
        };

        if (! $mime) {
            return null;
        }

        $binary = @file_get_contents($path);

        if ($binary === false || $binary === '') {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }

    private function qrDataUri(string $url): ?string
    {
        try {
            if (! extension_loaded('gd')) {
                return null;
            }

            $options = new QROptions([
                'outputInterface' => QRGdImagePNG::class,
                'scale' => 4,
                'outputBase64' => true,
                'addQuietzone' => true,
            ]);

            $dataUri = (new QRCode($options))->render($url);

            return is_string($dataUri) ? $dataUri : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function generateX509Pem(
        LeiCertificate $certificate,
        LeiApplication $application,
        LeiBusinessSetting $settings,
    ): string {
        $config = $this->opensslConfig();
        $days = max(1, (int) ($certificate->valid_from?->diffInDays($certificate->valid_until) ?: 365));

        $dn = [
            'countryName' => $this->countryCode($application->country),
            'organizationName' => $this->sanitizeDn($application->entity_name),
            'organizationalUnitName' => 'LEI Registry',
            'commonName' => $this->sanitizeDn($application->lei_number ?? 'LEI'),
        ];

        $privkey = openssl_pkey_new($config);
        if ($privkey === false) {
            throw new \RuntimeException('OpenSSL key generation failed: '.$this->opensslErrors());
        }

        $csr = openssl_csr_new($dn, $privkey, $config);
        if ($csr === false) {
            throw new \RuntimeException('OpenSSL CSR generation failed: '.$this->opensslErrors());
        }

        $sscert = openssl_csr_sign($csr, null, $privkey, $days, $config);
        if ($sscert === false) {
            throw new \RuntimeException('OpenSSL certificate signing failed: '.$this->opensslErrors());
        }

        openssl_x509_export($sscert, $pem);

        $dir = 'certificates/'.$application->id;
        $path = $dir.'/x509-'.$certificate->serial_number.'.pem';
        Storage::disk('local')->put($path, $pem);

        return $path;
    }

    /**
     * OpenSSL on Windows/XAMPP requires an explicit openssl.cnf path.
     */
    private function opensslConfig(): array
    {
        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $cnf = env('OPENSSL_CONF');
        if (! $cnf || ! is_file($cnf)) {
            $candidates = [
                'C:/xampp/apache/conf/openssl.cnf',
                'C:/xampp/php/extras/ssl/openssl.cnf',
                'C:/xampp/php/extras/openssl/openssl.cnf',
                'C:/laragon/bin/apache/apache-2.4.62/conf/openssl.cnf',
                'C:/laragon/etc/ssl/openssl.cnf',
            ];
            if ($ini = php_ini_loaded_file()) {
                $candidates[] = dirname($ini).'/extras/ssl/openssl.cnf';
            }
            foreach ($candidates as $candidate) {
                if (is_file($candidate)) {
                    $cnf = $candidate;
                    break;
                }
            }
        }

        if ($cnf && is_file($cnf)) {
            $config['config'] = $cnf;
            putenv('OPENSSL_CONF='.$cnf);
        }

        return $config;
    }

    private function opensslErrors(): string
    {
        $errors = [];
        while ($msg = openssl_error_string()) {
            $errors[] = $msg;
        }

        return $errors !== [] ? implode('; ', $errors) : 'unknown OpenSSL error';
    }

    private function sanitizeDn(string $value): string
    {
        $value = str_replace([',', '=', '+', '<', '>', '#', ';'], '', $value);

        return Str::limit(trim($value), 64, '');
    }

    private function storeFallbackX509Pem(
        LeiCertificate $certificate,
        LeiApplication $application,
        string $signatureHash,
    ): string {
        $pem = "-----BEGIN LEI CERTIFICATE METADATA-----\n"
            ."Serial: {$certificate->serial_number}\n"
            ."LEI: {$application->lei_number}\n"
            ."SHA-256: {$signatureHash}\n"
            ."-----END LEI CERTIFICATE METADATA-----\n";

        $path = 'certificates/'.$application->id.'/x509-'.$certificate->serial_number.'.pem';
        Storage::disk('local')->put($path, $pem);

        return $path;
    }

    private function countryCode(string $country): string
    {
        $map = [
            'india' => 'IN',
            'united kingdom' => 'GB',
            'united states' => 'US',
            'germany' => 'DE',
            'singapore' => 'SG',
        ];

        return $map[strtolower(trim($country))] ?? 'IN';
    }

    private function extractCity(string $address): string
    {
        $parts = array_map('trim', explode(',', $address));

        return $parts[0] ?: 'N/A';
    }

    private function formatRegisteredAddress(?string $address, ?string $country): ?string
    {
        $address = trim((string) $address);
        $country = trim((string) $country);

        if ($address === '' && $country === '') {
            return null;
        }

        if ($address === '') {
            return $country;
        }

        if ($country !== '' && ! str_contains(strtolower($address), strtolower($country))) {
            return $address.' | '.$country;
        }

        return $address;
    }
}
