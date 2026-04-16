<?php

declare(strict_types=1);

namespace Eram\Abzar\Format;

use Eram\Abzar\Digits\DigitConverter;
use Eram\Abzar\Internal\ErrorInput;

final class NumberFormatter
{
    private function __construct()
    {
    }

    public static function withSeparators(int|float|string $number, string $separator = ','): string
    {
        if (is_string($number)) {
            $number = DigitConverter::toEnglish($number);
            $number = str_replace([',', '٬'], '', $number);
        }

        $numberStr = (string) $number;

        if (!preg_match('/^-?\d+(\.\d+)?$/', $numberStr)) {
            throw new \InvalidArgumentException(
                'مقدار ورودی عددی معتبر نیست: ' . ErrorInput::truncate($numberStr, 32)
            );
        }

        $negative = str_starts_with($numberStr, '-');
        if ($negative) {
            $numberStr = substr($numberStr, 1);
        }

        $parts = explode('.', $numberStr, 2);
        $integerPart = $parts[0];
        $decimalPart = $parts[1] ?? null;

        $integerPart = (string) preg_replace('/\B(?=(\d{3})+(?!\d))/', $separator, $integerPart);

        $result = $integerPart;
        if ($decimalPart !== null) {
            $result .= '.' . $decimalPart;
        }

        if ($negative) {
            $result = '-' . $result;
        }

        return $result;
    }
}
