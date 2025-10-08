<?php

namespace App\Support\Formatters;

use NumberFormatter;

class MoneyFormatter
{
    public static function pen(null|float|int|string $amount, bool $withSymbol = true): string
    {
        $numericAmount = is_string($amount) ? (float) $amount : ($amount ?? 0);

        $formatter = new NumberFormatter('es_PE', NumberFormatter::CURRENCY);
        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 2);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);

        $formatted = $formatter->formatCurrency($numericAmount, 'PEN');

        if (! $withSymbol) {
            return trim(preg_replace('/^S\/[\h\s]*/u', '', $formatted) ?: '');
        }

        return $formatted;
    }
}
