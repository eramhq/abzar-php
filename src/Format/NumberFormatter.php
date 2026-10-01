<?php

declare(strict_types=1);

namespace Eram\Abzar\Format;

use Eram\Abzar\Digits\DigitConverter;
use Eram\Abzar\Exception\FormatException;
use Eram\Abzar\Validation\ErrorCode;

final class NumberFormatter
{
    private function __construct()
    {
    }

    /**
     * Group the integer part in thousands. String input may carry Persian /
     * Arabic digits, existing grouping ({@code ,} {@code ،} {@code ٬} or any
     * whitespace — including NBSP), the Arabic decimal separator {@code ٫},
     * and a leading {@code +}; so the output of {@see \Eram\Abzar\Money\Currency::format()}
     * round-trips.
     *
     * @throws FormatException when the input isn't a plain decimal number.
     */
    public static function withSeparators(int|float|string $number, string $separator = ','): string
    {
        if (is_string($number)) {
            $number = str_replace('٫', '.', DigitConverter::toEnglish(trim($number)));
            $number = preg_replace('/[\s,،٬]/u', '', $number) ?? $number;
            if (preg_match('/^\+\d/', $number)) {
                $number = substr($number, 1);
            }
        }

        $numberStr = (string) $number;

        if (!preg_match('/^-?\d+(\.\d+)?$/', $numberStr)) {
            throw FormatException::forInput(ErrorCode::NUMBER_FORMATTER_INVALID, $numberStr, 32);
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
