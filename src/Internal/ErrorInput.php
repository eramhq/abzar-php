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
     * Characters stripped from digit-bearing input on top of Unicode whitespace:
     * ASCII and Unicode dashes / minus, the zero-width joiners, the bidi marks
     * and embedding / isolate controls RTL apps wrap pasted numbers in, soft
     * hyphen, word joiner and BOM. NBSP (U+00A0) and narrow NBSP (U+202F) are
     * covered by {@code \s} under the {@code /u} flag.
     */
    private const NOISE_CLASS = '\s\-\x{00AD}\x{061C}\x{200B}-\x{200F}\x{2010}-\x{2015}\x{202A}-\x{202E}'
        . '\x{2060}\x{2066}-\x{2069}\x{2212}\x{FE63}\x{FEFF}\x{FF0D}';

    /**
     * Canonicalize digit-bearing input: trim, fold Persian / Arabic digits to
     * ASCII, and remove whitespace (including NBSP), dashes, invisible
     * formatting marks, and any additional literal characters listed in
     * $extraCharClass (e.g. "()." — taken literally, not as regex syntax).
     */
    public static function digits(string $value, string $extraCharClass = ''): string
    {
        $value = DigitConverter::toEnglish(trim($value));
        $extra = preg_quote($extraCharClass, '/');

        // Invalid UTF-8 makes the /u pattern fail; fall back to the ASCII-only
        // class so malformed input still reaches the validator's own checks.
        return preg_replace('/[' . self::NOISE_CLASS . $extra . ']/u', '', $value)
            ?? (string) preg_replace('/[\s\-' . $extra . ']/', '', $value);
    }
}
