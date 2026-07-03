<?php

namespace App\Services;

use App\Models\LeiApplication;
use App\Models\LeiBusinessSetting;
use App\Models\User;

class LeiCodeGenerator
{
    /**
     * Generate a valid, unique 20-character LEI code (ISO 17442).
     *
     * Structure:
     *   Chars  1–4  : LOU prefix (admin-configured, e.g. "5493")
     *   Chars  5–6  : Reserved — always "00"
     *   Chars  7–18 : 12-char alphanumeric entity ID (system-generated, unique)
     *   Chars 19–20 : Check digits (ISO 7064 MOD 97-10)
     */
    public function generate(): string
    {
        $prefix = $this->louPrefix();

        do {
            $entityId   = $this->generateEntityId();
            $base       = strtoupper($prefix . '00' . $entityId); // 18 chars
            $checkDigits = $this->calculateCheckDigits($base);
            $lei        = $base . $checkDigits;                   // 20 chars
        } while (
            LeiApplication::where('lei_number', $lei)->exists()
            || User::where('lei_number', $lei)->exists()
        );

        return $lei;
    }

    /**
     * Return the 4-character LOU prefix from admin business settings.
     * Falls back to "5493" (LEIL India prefix) if not configured.
     */
    public function louPrefix(): string
    {
        $prefix = LeiBusinessSetting::current()->lou_prefix ?? '5493';

        // Keep only uppercase alphanumeric chars, padded/trimmed to exactly 4
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $prefix));
        $prefix = substr(str_pad($prefix, 4, '0'), 0, 4);

        return $prefix;
    }

    /**
     * Calculate ISO 17442 check digits using ISO 7064 MOD 97-10.
     *
     * @param  string $base  Exactly 18 uppercase alphanumeric characters.
     * @return string        Two-digit string in range "02"–"98".
     */
    public function calculateCheckDigits(string $base): string
    {
        // Step 1: Convert each character → digits (letters A=10 … Z=35, digits as-is)
        $numeric = '';
        foreach (str_split(strtoupper($base)) as $char) {
            $numeric .= ctype_alpha($char)
                ? (string) (ord($char) - 55)   // A=10, B=11 … Z=35
                : $char;
        }

        // Step 2: Append "00" before computing the remainder
        $numeric .= '00';

        // Step 3: Block-wise mod-97 to avoid PHP integer overflow
        $remainder = 0;
        foreach (str_split($numeric) as $digit) {
            $remainder = ($remainder * 10 + (int) $digit) % 97;
        }

        // Step 4: Check digits = 98 − remainder, zero-padded to 2 chars
        $checkDigits = 98 - $remainder;

        return str_pad((string) $checkDigits, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Verify that a 20-character string is a valid ISO 17442 LEI.
     * The full numeric string mod 97 must equal 1.
     */
    public function verify(string $lei): bool
    {
        if (strlen($lei) !== 20 || ! ctype_alnum($lei)) {
            return false;
        }

        $numeric = '';
        foreach (str_split(strtoupper($lei)) as $char) {
            $numeric .= ctype_alpha($char) ? (string) (ord($char) - 55) : $char;
        }

        $remainder = 0;
        foreach (str_split($numeric) as $digit) {
            $remainder = ($remainder * 10 + (int) $digit) % 97;
        }

        return $remainder === 1;
    }

    /**
     * Generate a random 12-character alphanumeric entity ID (uppercase, no vowels).
     * Excluding vowels (A, E, I, O, U) follows LEIL/GLEIF LOU convention.
     */
    private function generateEntityId(): string
    {
        $chars = 'BCDFGHJKLMNPQRSTVWXYZ0123456789';
        $id    = '';

        for ($i = 0; $i < 12; $i++) {
            $id .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $id;
    }
}
