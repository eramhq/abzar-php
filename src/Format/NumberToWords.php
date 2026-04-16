<?php

declare(strict_types=1);

namespace Eram\Abzar\Format;

final class NumberToWords
{
    private function __construct()
    {
    }

    private const ONES     = PersianNumerals::ONES;
    private const TEENS    = PersianNumerals::TEENS;
    private const TENS     = PersianNumerals::TENS;
    private const HUNDREDS = PersianNumerals::HUNDREDS;
    private const SCALES   = PersianNumerals::SCALES;

    public static function convert(int|float $number): string
    {
        if ($number === 0 || $number === 0.0) {
            return 'صفر';
        }

        $prefix = '';
        if ($number < 0) {
            $prefix = 'منفی ';
            $number = abs($number);
        }

        if (is_float($number)) {
            $str = number_format($number, 10, '.', '');
            $parts = explode('.', $str);
            $integerPart = (int) $parts[0];
            $decimalPart = isset($parts[1]) ? rtrim($parts[1], '0') : '';

            $result = $integerPart === 0 && $decimalPart !== ''
                ? ''
                : self::convertInteger($integerPart);

            if ($decimalPart !== '') {
                $leadingZeros = strspn($decimalPart, '0');
                $significant  = substr($decimalPart, $leadingZeros);

                if ($significant !== '') {
                    $separator = $result !== '' ? ' ممیز ' : 'صفر ممیز ';
                    $zeroWords = $leadingZeros > 0
                        ? str_repeat('صفر ', $leadingZeros)
                        : '';
                    $result .= $separator . $zeroWords . self::convertInteger((int) $significant);
                }
            }

            return $prefix . ($result !== '' ? $result : 'صفر');
        }

        return $prefix . self::convertInteger((int) $number);
    }

    private static function convertInteger(int $number): string
    {
        if ($number === 0) {
            return 'صفر';
        }

        $groups = [];
        while ($number > 0) {
            $groups[] = $number % 1000;
            $number = intdiv($number, 1000);
        }

        $parts = [];
        for ($i = count($groups) - 1; $i >= 0; $i--) {
            if ($groups[$i] === 0) {
                continue;
            }

            $groupText = self::convertGroup($groups[$i]);
            if ($i > 0) {
                if (!isset(self::SCALES[$i])) {
                    throw new \OverflowException('Number exceeds the largest supported Persian scale.');
                }
                $groupText .= ' ' . self::SCALES[$i];
            }

            $parts[] = $groupText;
        }

        return implode(' و ', $parts);
    }

    private static function convertGroup(int $number): string
    {
        $parts = [];

        $hundreds = intdiv($number, 100);
        if ($hundreds > 0) {
            $parts[] = self::HUNDREDS[$hundreds];
        }

        $remainder = $number % 100;

        if ($remainder >= 10 && $remainder <= 19) {
            $parts[] = self::TEENS[$remainder - 10];
        } else {
            $tens = intdiv($remainder, 10);
            $ones = $remainder % 10;

            if ($tens > 0) {
                $parts[] = self::TENS[$tens];
            }
            if ($ones > 0) {
                $parts[] = self::ONES[$ones];
            }
        }

        return implode(' و ', $parts);
    }
}
