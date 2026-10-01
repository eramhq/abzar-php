<?php

declare(strict_types=1);

namespace Eram\Abzar\Format;

use Eram\Abzar\Digits\DigitConverter;
use Eram\Abzar\Exception\FormatException;
use Eram\Abzar\Validation\ErrorCode;

final class OrdinalNumber
{
    private function __construct()
    {
    }

    public static function toWord(int $n): string
    {
        if ($n < 1) {
            throw FormatException::forInput(ErrorCode::ORDINAL_NUMBER_NON_POSITIVE, (string) $n);
        }

        $word = NumberToWords::convert($n);

        return self::addSuffix($word);
    }

    /**
     * Numeric ordinal: {@code toShort(43)} → {@code ۴۳ام}. Pass
     * {@code $persianDigits: false} for ASCII digits, typically with a
     * matching suffix ({@code toShort(43, false, 'rd')} → {@code 43rd}).
     */
    public static function toShort(int $n, bool $persianDigits = true, string $suffix = 'ام'): string
    {
        if ($n < 1) {
            throw FormatException::forInput(ErrorCode::ORDINAL_NUMBER_NON_POSITIVE, (string) $n);
        }

        $str = (string) $n;

        if ($persianDigits) {
            $str = DigitConverter::toPersian($str);
        }

        return $str . $suffix;
    }

    public static function addSuffix(string $persianWord): string
    {
        $word = trim($persianWord);

        if ($word === '') {
            throw FormatException::forInput(ErrorCode::ORDINAL_NUMBER_EMPTY_INPUT, $persianWord);
        }

        if (str_ends_with($word, 'سه')) {
            return mb_substr($word, 0, mb_strlen($word) - 2) . 'سوم';
        }

        // سی → سی‌ام: joined with ZWNJ, per standard Persian orthography.
        if (str_ends_with($word, 'ی')) {
            return $word . "\u{200C}ام";
        }

        return $word . 'م';
    }
}
