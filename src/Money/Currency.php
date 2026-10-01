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

    /**
     * An {@see Amount} is rendered in $unit; a Toman rendering of a rial
     * amount that isn't a multiple of 10 keeps its fraction (12,345 rials →
     * {@code ۱،۲۳۴.۵ تومان}). Scalars are formatted as given.
     */
    public static function format(
        int|float|string|Amount $amount,
        Unit $unit = Unit::TOMAN,
        bool $persianDigits = true,
        bool $withUnit = true,
        string $separator = '،',
    ): string {
        if ($amount instanceof Amount) {
            $rials  = $amount->inRials();
            $amount = match (true) {
                $unit === Unit::RIAL => $rials,
                $rials % 10 === 0    => intdiv($rials, 10),
                default              => intdiv($rials, 10) . '.' . ($rials % 10),
            };
        }

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

        // Integer `/` stays int when exact (12340 → 1234) and yields a float
        // otherwise (1235 → 123.5).
        return $from === Unit::TOMAN ? $amount * 10 : $amount / 10;
    }
}
