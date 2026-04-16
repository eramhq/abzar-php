<?php

declare(strict_types=1);

namespace Eram\Abzar\Format;

/**
 * Parse Persian number words into a numeric value. Inverse of
 * {@see NumberToWords::convert()}.
 *
 *  * Accepts leading {@code منفی} for negative numbers.
 *  * {@code ممیز} flips into fractional mode; leading {@code صفر} tokens count
 *    as zero-padding (so "سه ممیز صفر پنج" yields 3.05). Result is a {@code float}.
 *  * Returns {@code null} on unparseable input (mixed digits + words,
 *    plural scales like {@code میلیون ها}, empty whitespace).
 */
final class WordsToNumber
{
    private function __construct()
    {
    }

    public static function parse(string $text): int|float|null
    {
        $text = trim($text);

        if ($text === '') {
            return null;
        }

        if ($text === 'صفر') {
            return 0;
        }

        $negative = false;
        if (str_starts_with($text, 'منفی ')) {
            $negative = true;
            $text = substr($text, strlen('منفی '));
        }

        $parts = preg_split('/ ممیز /u', $text, 2);

        $intPart = self::parseInteger($parts[0] ?? '');
        if ($intPart === null) {
            return null;
        }

        if (isset($parts[1])) {
            $fracTokens = self::tokenize($parts[1]);
            if ($fracTokens === null || $fracTokens === []) {
                return null;
            }

            $leadingZeros = 0;
            while (isset($fracTokens[$leadingZeros]) && $fracTokens[$leadingZeros] === 'صفر') {
                $leadingZeros++;
            }
            $remainder = array_slice($fracTokens, $leadingZeros);

            if ($remainder === []) {
                $value = (float) $intPart;

                return $negative ? -$value : $value;
            }

            $fracInt = self::sumTokens($remainder);
            if ($fracInt === null || $fracInt === 0) {
                return null;
            }
            $remainingDigits = (int) floor(log10($fracInt)) + 1;
            $totalDigits     = $leadingZeros + $remainingDigits;
            $value           = (float) $intPart + ($fracInt / (10 ** $totalDigits));

            return $negative ? -$value : $value;
        }

        return $negative ? -$intPart : $intPart;
    }

    private static function parseInteger(string $text): ?int
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        if ($text === 'صفر') {
            return 0;
        }

        $tokens = self::tokenize($text);

        return $tokens === null ? null : self::sumTokens($tokens);
    }

    /**
     * Split on " و " (Persian "and" conjunction) and whitespace/ZWNJ.
     *
     * @return list<string>|null
     */
    private static function tokenize(string $text): ?array
    {
        $tokens = preg_split('/(?:\s+و\s+|\s+|\x{200C}+)/u', trim($text));
        if ($tokens === false) {
            return null;
        }

        return array_values(array_filter($tokens, static fn (string $t): bool => $t !== ''));
    }

    /**
     * @param list<string> $tokens
     */
    private static function sumTokens(array $tokens): ?int
    {
        $lookup  = self::lookup();
        $total   = 0;
        $segment = 0;

        foreach ($tokens as $token) {
            if (!isset($lookup[$token])) {
                return null;
            }
            [$kind, $value] = $lookup[$token];

            if ($kind === 'scale') {
                if ($value === 1000) {
                    $segment = max($segment, 1) * 1000;
                } else {
                    $total  += max($segment, 1) * $value;
                    $segment = 0;
                }
            } else {
                $segment += $value;
            }
        }

        return $total + $segment;
    }

    /**
     * @return array<string, array{0: 'unit'|'scale', 1: int}>
     */
    private static function lookup(): array
    {
        static $map = null;
        if ($map !== null) {
            return $map;
        }

        $map = [];

        foreach (PersianNumerals::ONES as $i => $word) {
            if ($word !== '') {
                $map[$word] = ['unit', $i];
            }
        }
        foreach (PersianNumerals::TEENS as $i => $word) {
            $map[$word] = ['unit', $i + 10];
        }
        foreach (PersianNumerals::TENS as $i => $word) {
            if ($word !== '') {
                $map[$word] = ['unit', $i * 10];
            }
        }
        foreach (PersianNumerals::HUNDREDS as $i => $word) {
            if ($word !== '') {
                $map[$word] = ['unit', $i * 100];
            }
        }
        // Common alternate forms.
        $map['صد']   = ['unit', 100];
        $map['هزار'] = ['scale', 1000];

        $scales = [
            2 => 1_000_000,
            3 => 1_000_000_000,
            4 => 1_000_000_000_000,
            5 => 1_000_000_000_000_000,
            6 => 1_000_000_000_000_000_000,
        ];
        foreach ($scales as $i => $multiplier) {
            $map[PersianNumerals::SCALES[$i]] = ['scale', $multiplier];
        }

        return $map;
    }
}
