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
    private const RANK_ONES     = 0;
    private const RANK_TEENS    = 1;
    private const RANK_TENS     = 2;
    private const RANK_HUNDREDS = 3;
    private const RANK_SCALE    = 4;

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
     * Sum a token stream left to right. Within each sub-thousand group the
     * units must step down in rank — hundreds, then tens, then ones, or a
     * single teen — so "دو سه" or "بیست سی" are rejected rather than summed.
     * {@code هزار} multiplies the group in front of it; larger scales must
     * appear in decreasing order ("هزار میلیارد" is allowed, "یک میلیون دو
     * میلیون" isn't). Returns null on any rank violation or when the value
     * would exceed {@code PHP_INT_MAX}.
     *
     * @param list<string> $tokens
     */
    private static function sumTokens(array $tokens): ?int
    {
        $lookup    = self::lookup();
        $total     = 0;      // sum of completed big-scale (≥ million) groups
        $thousands = null;   // current group's "N هزار" part, once seen
        $small     = 0;      // current sub-thousand accumulator
        $ceiling   = self::RANK_SCALE;
        $lastScale = PHP_INT_MAX;
        $prevOnes  = false;

        foreach ($tokens as $token) {
            if (!isset($lookup[$token])) {
                return null;
            }
            [$rank, $value] = $lookup[$token];

            if ($rank === self::RANK_SCALE) {
                if ($value === 1000) {
                    if ($thousands !== null) {
                        return null;
                    }
                    $thousands = max($small, 1) * 1000;
                } else {
                    if ($value >= $lastScale) {
                        return null;
                    }
                    $group = ($thousands ?? 0) + $small;
                    $group = $group === 0 ? 1 : $group;
                    if ($group > intdiv(PHP_INT_MAX, $value)) {
                        return null;
                    }
                    $product = $group * $value;
                    if ($total > PHP_INT_MAX - $product) {
                        return null;
                    }
                    $total    += $product;
                    $lastScale = $value;
                    $thousands = null;
                }
                $small    = 0;
                $ceiling  = self::RANK_SCALE;
                $prevOnes = false;
                continue;
            }

            // Colloquial split hundreds: "سه صد" = 300, "یک صد" = 100.
            if ($token === 'صد' && $prevOnes && $small < 10) {
                $small    *= 100;
                $ceiling   = self::RANK_HUNDREDS;
                $prevOnes  = false;
                continue;
            }

            if ($rank >= $ceiling) {
                return null;
            }
            $small   += $value;
            // After hundreds: tens / teens / ones may follow. After tens: only
            // ones. After a teen or a ones digit: nothing until the next scale.
            $ceiling  = match ($rank) {
                self::RANK_HUNDREDS => self::RANK_HUNDREDS,
                self::RANK_TENS     => self::RANK_TEENS,
                default             => self::RANK_ONES,
            };
            $prevOnes = $rank === self::RANK_ONES;
        }

        $rest = ($thousands ?? 0) + $small;

        return $total > PHP_INT_MAX - $rest ? null : $total + $rest;
    }

    /**
     * @return array<string, array{0: int, 1: int}> token → [rank, value]
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
                $map[$word] = [self::RANK_ONES, $i];
            }
        }
        foreach (PersianNumerals::TEENS as $i => $word) {
            $map[$word] = [self::RANK_TEENS, $i + 10];
        }
        foreach (PersianNumerals::TENS as $i => $word) {
            if ($word !== '') {
                $map[$word] = [self::RANK_TENS, $i * 10];
            }
        }
        foreach (PersianNumerals::HUNDREDS as $i => $word) {
            if ($word !== '') {
                $map[$word] = [self::RANK_HUNDREDS, $i * 100];
            }
        }
        // Common alternate forms, including the spellings persian-tools reads
        // (شیش, چارصد) or writes (کوآدریلیون). Each maps to a single value.
        $map['شیش']   = [self::RANK_ONES, 6];
        $map['صد']    = [self::RANK_HUNDREDS, 100];
        $map['چارصد'] = [self::RANK_HUNDREDS, 400];
        $map['هزار']  = [self::RANK_SCALE, 1000];

        $scales = [
            2 => 1_000_000,
            3 => 1_000_000_000,
            4 => 1_000_000_000_000,
            5 => 1_000_000_000_000_000,
            6 => 1_000_000_000_000_000_000,
        ];
        foreach ($scales as $i => $multiplier) {
            $map[PersianNumerals::SCALES[$i]] = [self::RANK_SCALE, $multiplier];
        }
        $map['بیلیون']     = [self::RANK_SCALE, 1_000_000_000];
        $map['کوآدریلیون'] = [self::RANK_SCALE, 1_000_000_000_000_000];

        return $map;
    }
}
