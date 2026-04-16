<?php

declare(strict_types=1);

namespace Eram\Abzar\Format;

use Eram\Abzar\AbzarFormatException;
use Eram\Abzar\Digits\DigitConverter;
use Eram\Abzar\Validation\ErrorCode;

final class OrdinalNumber
{
    private function __construct()
    {
    }

    public static function toWord(int $n): string
    {
        if ($n < 1) {
            throw AbzarFormatException::forInput(ErrorCode::ORDINAL_NUMBER_NON_POSITIVE, (string) $n);
        }

        $word = NumberToWords::convert($n);

        return self::addSuffix($word);
    }

    public static function toShort(int $n, string $digits = 'persian'): string
    {
        if ($n < 1) {
            throw AbzarFormatException::forInput(ErrorCode::ORDINAL_NUMBER_NON_POSITIVE, (string) $n);
        }

        $str = (string) $n;

        if ($digits === 'persian') {
            $str = DigitConverter::toPersian($str);
        }

        return $str . 'ام';
    }

    public static function addSuffix(string $persianWord): string
    {
        $word = trim($persianWord);

        if ($word === '') {
            throw AbzarFormatException::forInput(ErrorCode::ORDINAL_NUMBER_EMPTY_INPUT, $persianWord);
        }

        if (str_ends_with($word, 'سه')) {
            return mb_substr($word, 0, mb_strlen($word) - 2) . 'سوم';
        }

        if (str_ends_with($word, 'ی')) {
            return $word . ' اُم';
        }

        return $word . 'م';
    }
}
