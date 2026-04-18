<?php

declare(strict_types=1);

namespace Eram\Abzar\Money;

use Eram\Abzar\Digits\DigitConverter;
use Eram\Abzar\Format\NumberFormatter;

/**
 * Toman / Rial formatter — a thin opinionated wrapper over
 * {@see NumberFormatter::withSeparators()} with the Persian currency unit
 * word appended. {@see self::convert()} covers the Toman ↔ Rial ×10 / ÷10 pair.
 */
final class Currency
{
    private function __construct()
    {
    }

    public static function format(
        int|float|string $amount,
        Unit $unit = Unit::TOMAN,
        bool $persianDigits = true,
        bool $withUnit = true,
        string $separator = '،',
    ): string {
        $formatted = NumberFormatter::withSeparators($amount, $separator);

        if ($persianDigits) {
            $formatted = DigitConverter::toPersian($formatted);
        }

        if ($withUnit) {
            $formatted .= ' ' . $unit->persianName();
        }

        return $formatted;
    }

    public static function convert(
        int|float $amount,
        Unit $from,
        Unit $to,
    ): int|float {
        if ($from === $to) {
            return $amount;
        }

        if ($from === Unit::TOMAN && $to === Unit::RIAL) {
            return is_int($amount) ? $amount * 10 : $amount * 10.0;
        }

        if (is_int($amount) && $amount % 10 === 0) {
            return intdiv($amount, 10);
        }

        return $amount / 10;
    }
}
