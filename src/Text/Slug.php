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
        $text = (string) preg_replace('/[\s_]+/u', '-', $text);
        $text = (string) preg_replace('/[^\x{0600}-\x{06FF}\x{200C}a-z0-9\-]/u', '', $text);
        $text = (string) preg_replace('/-+/', '-', $text);

        return trim($text, '-');
    }

    private static function defaultNormalizer(): CharNormalizer
    {
        static $normalizer = null;
        return $normalizer ??= new CharNormalizer();
    }
}
