<?php

namespace App\Support;

class ReciboDesembolsoFormatter
{
    public static function currency($value): string
    {
        $number = is_numeric($value) ? (float) $value : 0.0;

        return '$' . number_format($number, 2, '.', ',');
    }

    public static function currencyNullable($value): string
    {
        return is_numeric($value)
            ? self::currency($value)
            : 'N/A';
    }

    public static function percentOrNA($value): string
    {
        return is_numeric($value)
            ? number_format((float) $value, 2, '.', ',') . '%'
            : 'N/A';
    }

    public static function textOrNA($value): string
    {
        $text = trim((string) ($value ?? ''));

        return $text !== '' ? $text : 'N/A';
    }
}
