<?php

declare(strict_types=1);

namespace Eram\Abzar\Text;

use Eram\Abzar\Digits\DigitConverter;

class CharNormalizer
{
    /** @var array<string> */
    private array $search;

    /** @var array<string> */
    private array $replace;

    public function __construct(bool $tehMarbuta = false)
    {
        $this->search = array_merge(
            ["\u{064A}", "\u{0643}"], // Arabic Yeh, Arabic Kaf
            DigitConverter::ARABIC_DIGITS
        );

        $this->replace = array_merge(
            ["\u{06CC}", "\u{06A9}"], // Persian Yeh, Persian Kaf
            DigitConverter::PERSIAN_DIGITS
        );

        if ($tehMarbuta) {
            $this->search[]  = "\u{0629}"; // Arabic Teh Marbuta
            $this->replace[] = "\u{0647}"; // Persian Heh
        }
    }

    public function normalize(string $text): string
    {
        return str_replace($this->search, $this->replace, $text);
    }

    public function normalizeContent(string $html): string
    {
        return HtmlSegmenter::transformText($html, $this->normalize(...));
    }

    public function normalizeForSearch(string $text): string
    {
        $text = $this->normalize($text);

        return DigitConverter::toEnglish($text);
    }
}
