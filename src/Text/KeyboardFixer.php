<?php

declare(strict_types=1);

namespace Eram\Abzar\Text;

/**
 * Swap between English QWERTY and the standard Iranian Persian keyboard layout
 * (fa-IR). Typical use case: a user typed with the wrong layout — e.g. pressed
 * the keys for "سلام" while QWERTY was active and produced "sghl".
 *
 * Upper-case letters follow the Shift layer of the ISIRI 9147 standard layout
 * (Shift+H → آ, Shift+C → ژ, Shift+M → ء, Shift+B → ZWNJ, tashkeel on the top
 * row, …) instead of being folded to lower case.
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

    /**
     * ISIRI 9147 Shift layer for the letter keys.
     *
     * @var array<string, string>
     */
    private const EN_TO_FA_SHIFT = [
        'Q' => "\u{0652}", 'W' => "\u{064C}", 'E' => "\u{064D}", 'R' => "\u{064B}", 'T' => "\u{064F}",
        'Y' => "\u{0650}", 'U' => "\u{064E}", 'I' => "\u{0651}", 'O' => ']', 'P' => '[',
        'A' => 'ؤ', 'S' => 'ئ', 'D' => 'ي', 'F' => 'إ', 'G' => 'أ', 'H' => 'آ',
        'J' => 'ة', 'K' => '»', 'L' => '«',
        'Z' => 'ك', 'X' => "\u{0653}", 'C' => 'ژ', 'V' => "\u{0670}", 'B' => "\u{200C}",
        'N' => "\u{0654}", 'M' => 'ء',
    ];

    private function __construct()
    {
    }

    public static function enToFa(string $text): string
    {
        return strtr($text, self::EN_TO_FA + self::EN_TO_FA_SHIFT);
    }

    public static function faToEn(string $text): string
    {
        static $reverse = null;
        // Shift+O / Shift+P yield ASCII brackets, which in Persian text are far
        // more likely literal than a layout slip — leave them out of the reverse map.
        $reverse ??= array_flip(self::EN_TO_FA) + array_flip(array_diff_key(self::EN_TO_FA_SHIFT, ['O' => 0, 'P' => 0]));

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
