<?php

declare(strict_types=1);

namespace Eram\Abzar\Format;

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
            throw new \InvalidArgumentException(ErrorCode::ORDINAL_NUMBER_NON_POSITIVE->message());
        }

        $word = NumberToWords::convert($n);

        return self::addSuffix($word);
    }

    public static function toShort(int $n, string $digits = 'persian'): string
    {
        if ($n < 1) {
            throw new \InvalidArgumentException(ErrorCode::ORDINAL_NUMBER_NON_POSITIVE->message());
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
            throw new \InvalidArgumentException(ErrorCode::ORDINAL_NUMBER_EMPTY_INPUT->message());
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
