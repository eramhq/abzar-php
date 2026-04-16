<?php

declare(strict_types=1);

namespace Eram\Abzar\Internal;

use Eram\Abzar\Digits\DigitConverter;

/**
 * @internal Not covered by BC guarantees; do not depend on from outside abzar.
 */
final class ErrorInput
{
    private function __construct()
    {
    }

    /**
     * Strip control chars and cap the length of user input before interpolating
     * into an exception message. Exception strings often flow into log
     * aggregators, so we avoid leaking raw bytes verbatim.
     */
    public static function truncate(string $value, int $max): string
    {
        $safe = preg_replace('/[\x00-\x1F\x7F]+/u', '', $value) ?? '';
        if (mb_strlen($safe, 'UTF-8') > $max) {
            return mb_substr($safe, 0, $max, 'UTF-8') . '…';
        }
        return $safe;
    }

    /**
     * Canonicalize digit-bearing input: trim, fold Persian / Arabic digits to
     * ASCII, and remove whitespace, dashes, and any additional characters
     * in $extraCharClass (regex class body, without brackets — e.g. "()").
     */
    public static function digits(string $value, string $extraCharClass = ''): string
    {
        $value = DigitConverter::toEnglish(trim($value));

        return (string) preg_replace('/[\s\-' . $extraCharClass . ']/', '', $value);
    }
}
