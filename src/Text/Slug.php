<?php

namespace Eram\Abzar\Text;

use Eram\Abzar\Digits\DigitConverter;

class Slug
{
    public static function generate(string $text): string
    {
        static $normalizer = null;
        if ($normalizer === null) {
            $normalizer = new CharNormalizer();
        }

        $text = $normalizer->normalize($text);
        $text = DigitConverter::toEnglish($text);
        $text = mb_strtolower($text, 'UTF-8');
        $text = (string) preg_replace('/[\s_]+/u', '-', $text);
        $text = (string) preg_replace('/[^\x{0600}-\x{06FF}\x{200C}a-z0-9\-]/u', '', $text);
        $text = (string) preg_replace('/-+/', '-', $text);

        return trim($text, '-');
    }
}
