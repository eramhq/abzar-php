<?php

declare(strict_types=1);

namespace Eram\Abzar\Digits;

use Eram\Abzar\Text\HtmlSegmenter;

final class DigitConverter
{
    public const ENGLISH_DIGITS = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    public const PERSIAN_DIGITS = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    public const ARABIC_DIGITS  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    private function __construct()
    {
    }

    public static function toPersian(string $text): string
    {
        $text = str_replace(self::ENGLISH_DIGITS, self::PERSIAN_DIGITS, $text);
        $text = str_replace(self::ARABIC_DIGITS, self::PERSIAN_DIGITS, $text);

        return $text;
    }

    public static function toEnglish(string $text): string
    {
        $text = str_replace(self::PERSIAN_DIGITS, self::ENGLISH_DIGITS, $text);
        $text = str_replace(self::ARABIC_DIGITS, self::ENGLISH_DIGITS, $text);

        return $text;
    }

    public static function toArabic(string $text): string
    {
        $text = str_replace(self::ENGLISH_DIGITS, self::ARABIC_DIGITS, $text);
        $text = str_replace(self::PERSIAN_DIGITS, self::ARABIC_DIGITS, $text);

        return $text;
    }

    /**
     * Convert digits in HTML content, skipping tags, scripts, styles, and comments.
     */
    public static function convertContent(string $html): string
    {
        return HtmlSegmenter::transformText($html, self::toPersian(...));
    }
}
