<?php

declare(strict_types=1);

namespace Eram\Abzar\Text;

/**
 * Swap between English QWERTY and the standard Iranian Persian keyboard layout
 * (fa-IR). Typical use case: a user typed with the wrong layout — e.g. pressed
 * the keys for "سلام" while QWERTY was active and produced "sghl".
 *
 * Digits, whitespace, Persian / Arabic / kashida / ZWNJ are passed through as-is.
 */
final class KeyboardFixer
{
    /** @var array<string, string> */
    private const EN_TO_FA = [
        'q' => 'ض', 'w' => 'ص', 'e' => 'ث', 'r' => 'ق', 't' => 'ف', 'y' => 'غ',
        'u' => 'ع', 'i' => 'ه', 'o' => 'خ', 'p' => 'ح',
        'a' => 'ش', 's' => 'س', 'd' => 'ی', 'f' => 'ب', 'g' => 'ل', 'h' => 'ا',
        'j' => 'ت', 'k' => 'ن', 'l' => 'م',
        'z' => 'ظ', 'x' => 'ط', 'c' => 'ز', 'v' => 'ر', 'b' => 'ذ', 'n' => 'د',
        'm' => 'پ',
        '[' => 'ج', ']' => 'چ', ';' => 'ک', "'" => 'گ',
        ',' => 'و', '.' => '.', '/' => '/',
        '?' => '؟', '"' => '،',
    ];

    private function __construct()
    {
    }

    public static function enToFa(string $text): string
    {
        $lower = mb_strtolower($text, 'UTF-8');

        return strtr($lower, self::EN_TO_FA);
    }

    public static function faToEn(string $text): string
    {
        static $reverse = null;
        $reverse ??= array_flip(self::EN_TO_FA);

        return strtr($text, $reverse);
    }

    /**
     * Heuristic "did this user type with the wrong keyboard layout?" detector.
     *
     * True when the input is ASCII-letter-only and its vowel ratio is below
     * typical English (~35%+) — the fingerprint of a Persian word typed with
     * the English layout active ({@code sghl} for {@code سلام} has zero vowels).
     * False for already-Persian text, mixed-script input, and normal English.
     *
     * Character-script entropy, not grammar-aware. Callers should still give
     * users a way to opt out.
     */
    public static function detect(string $text): bool
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return false;
        }

        if (Script::hasPersian($trimmed, complex: true) || Script::hasArabic($trimmed)) {
            return false;
        }

        $lower   = mb_strtolower($trimmed, 'UTF-8');
        $letters = (string) preg_replace('/[^a-z]/', '', $lower);
        $total   = strlen($letters);
        if ($total < 2) {
            return false;
        }

        $vowels = (int) preg_match_all('/[aeiou]/', $letters);

        return ($vowels * 4) < $total;
    }
}
