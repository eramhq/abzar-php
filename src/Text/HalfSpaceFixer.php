<?php

declare(strict_types=1);

namespace Eram\Abzar\Text;

/**
 * Insert zero-width non-joiner (U+200C, "half space") inside Persian compound
 * words so display renders them tightly. {@code می روم} becomes {@code می‌روم};
 * {@code خانه ها} becomes {@code خانه‌ها}.
 *
 * Best-effort rule-based, not a grammar parser. The {@code می|نمی} prefix rule
 * binds to *any* Persian/Arabic-letter follower, not only verb stems —
 * distinguishing verb stems from nouns needs a verb-stem lexicon, which doesn't
 * fit abzar's zero-dependency charter. Noun-noun inputs like {@code می کتاب}
 * ({@code می} = "wine") will glue incorrectly; the fixer is the wrong pass for
 * poetry or text where non-verbal {@code می} is expected.
 */
final class HalfSpaceFixer
{
    private const ZWNJ = "\u{200C}";

    private const PREFIX_PATTERN = '/(^|\s)(نمی|می)\s+(\p{Arabic})/u';

    /**
     * Longer suffixes come first so the alternation doesn't match a shorter
     * prefix of a longer suffix (e.g. `ها` would shadow `هایشان`).
     */
    private const SUFFIX_PATTERN = '/(\p{Arabic})\s+(هایشان|هایمان|هایتان|هایی|هایم|هایت|هایش|ترین|تر|ها|اید|اند|ایم|ام|ای)(\s|$|[.,;!?؟،؛])/u';

    private function __construct()
    {
    }

    public static function fix(string $text): string
    {
        $text = (string) preg_replace(self::PREFIX_PATTERN, '$1$2' . self::ZWNJ . '$3', $text);
        $text = (string) preg_replace(self::SUFFIX_PATTERN, '$1' . self::ZWNJ . '$2$3', $text);

        return $text;
    }
}
