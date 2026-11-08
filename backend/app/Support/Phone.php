<?php

namespace App\Support;

class Phone
{
    /**
     * Normalize an Egyptian phone number:
     * - Converts Arabic-Indic and Eastern Arabic/Persian digits to standard digits
     * - Strips all non-digit characters
     * - Turns leading 0020, +20, or 20 into 0
     * - Turns leading 1 (10 digits) into 01...
     */
    public static function normalize(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $value = str_replace($eastern, $western, (string) $value);
        $value = str_replace($persian, $western, $value);

        $digits = preg_replace('/\D+/', '', $value);

        if (str_starts_with($digits, '0020')) {
            $digits = '0' . substr($digits, 4);
        } elseif (str_starts_with($digits, '20') && strlen($digits) >= 12) {
            $digits = '0' . substr($digits, 2);
        } elseif (str_starts_with($digits, '1') && strlen($digits) === 10) {
            $digits = '0' . $digits;
        }

        return $digits;
    }

    public static function isValid(?string $value): bool
    {
        return (bool) preg_match('/^01[0125]\d{8}$/', self::normalize($value));
    }
}
