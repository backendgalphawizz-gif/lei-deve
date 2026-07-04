<?php

namespace App\Support;

use App\Models\LeiBusinessSetting;
use Illuminate\Support\Number;

class CurrencyFormatter
{
    public static function settings(): LeiBusinessSetting
    {
        try {
            return LeiBusinessSetting::current();
        } catch (\Throwable) {
            return new LeiBusinessSetting(LeiBusinessSetting::defaults());
        }
    }

    public static function symbol(): string
    {
        return static::settings()->currency_symbol ?: '₹';
    }

    public static function code(): string
    {
        return static::settings()->currency_code ?: 'INR';
    }

    public static function locale(): string
    {
        return static::settings()->numberLocale();
    }

    public static function applyLocale(): void
    {
        Number::useLocale(static::locale());
        Number::useCurrency(static::code());
    }

    public static function formatNumber(float|int $number, ?int $precision = null): string
    {
        static::applyLocale();

        try {
            return Number::format($number, $precision, locale: static::locale());
        } catch (\Throwable) {
            return static::formatIndianFallback($number, $precision ?? 0);
        }
    }

    public static function format(float $amount, int $decimals = 2): string
    {
        return static::symbol().static::formatNumber($amount, $decimals);
    }

    /**
     * Format amount for DomPDF invoices (DejaVu Sans lacks the ₹ glyph).
     * Pair with Noto Sans via CurrencyFormatter::pdfFontPath() in the PDF view.
     */
    public static function pdfFontPath(): string
    {
        return public_path('fonts/NotoSans-Regular.ttf');
    }

    public static function formatPdf(float $amount, int $decimals = 2): string
    {
        return "\u{20B9}".number_format($amount, $decimals);
    }

    public static function formatSignedCompact(float $amount): string
    {
        $sign = $amount < 0 ? '-' : '+';
        $abs = abs($amount);

        if ($abs >= 100000) {
            return $sign.static::symbol().static::formatNumber($abs / 100000, 1).' L';
        }

        if ($abs >= 1000) {
            return $sign.static::symbol().static::formatNumber($abs / 1000, 1).'k';
        }

        return $sign.static::format($abs, $abs >= 100 ? 0 : 2);
    }

    public static function formatLarge(float $amount): string
    {
        $abs = abs($amount);

        if ($abs >= 10000000) {
            return static::symbol().static::formatNumber($abs / 10000000, 1).' Cr';
        }

        if ($abs >= 100000) {
            return static::symbol().static::formatNumber($abs / 100000, 1).' L';
        }

        return static::format($abs, 0);
    }

    private static function formatIndianFallback(float|int $number, int $decimals): string
    {
        $negative = $number < 0;
        $number = abs((float) $number);
        $formatted = number_format($number, $decimals, '.', '');
        $parts = explode('.', $formatted);
        $integer = $parts[0];
        $decimal = $parts[1] ?? null;

        $lastThree = substr($integer, -3);
        $rest = substr($integer, 0, -3);
        if ($rest !== '') {
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $integer = $rest.','.$lastThree;
        }

        $result = $decimal !== null && $decimals > 0 ? $integer.'.'.$decimal : $integer;

        return $negative ? '-'.$result : $result;
    }
}
