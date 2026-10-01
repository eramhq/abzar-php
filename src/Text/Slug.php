<?php

declare(strict_types=1);

namespace Eram\Abzar\Text;

final class Slug
{
    private function __construct()
    {
    }

    public static function generate(string $text, ?CharNormalizer $normalizer = null): string
    {
        if ($text === '') {
            return '';
        }

        $normalizer ??= self::defaultNormalizer();

        $text = $normalizer->normalizeForSearch($text);
        $text = mb_strtolower($text, 'UTF-8');
        // Persian punctuation (، ؛ ؟ ٪ ٫ ٬ ۔), kashida, and tashkeel / superscript
        // alef sit inside the Arabic block the whitelist below keeps, so drop
        // them explicitly. ZWNJ separates words visually, so it becomes "-".
        $text = (string) preg_replace('/[\x{060C}\x{061B}\x{061F}\x{066A}-\x{066C}\x{06D4}\x{0640}\x{064B}-\x{065F}\x{0670}]/u', '', $text);
        $text = (string) preg_replace('/[\s_\x{200C}]+/u', '-', $text);
        $text = (string) preg_replace('/[^\x{0600}-\x{06FF}a-z0-9\-]/u', '', $text);
        $text = (string) preg_replace('/-+/', '-', $text);

        return trim($text, '-');
    }

    private static function defaultNormalizer(): CharNormalizer
    {
        static $normalizer = null;
        return $normalizer ??= new CharNormalizer();
    }
}
