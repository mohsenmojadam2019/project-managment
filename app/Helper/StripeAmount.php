<?php

namespace App\Helper;

class StripeAmount
{
    private const ZERO_DECIMAL_CURRENCIES = [
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA',
        'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    public static function toMinorUnits(int|float|string $amount, string $currency): int
    {
        $currency = strtoupper(trim($currency));

        return in_array($currency, self::ZERO_DECIMAL_CURRENCIES, true)
            ? (int) round((float) $amount)
            : (int) round((float) $amount * 100);
    }
}
