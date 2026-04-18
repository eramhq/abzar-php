<?php

declare(strict_types=1);

namespace Eram\Abzar\Text;

use Eram\Abzar\Digits\DigitConverter;
use Eram\Abzar\Exception\EnvironmentException;
use Eram\Abzar\Validation\ErrorCode;

final class CharNormalizer
{
    /** @var list<string> */
    private array $search;

    /** @var list<string> */
    private array $replace;

    private readonly bool $stripTashkeel;
    private readonly bool $stripKashida;
    private readonly bool $stripBidiMarks;
    private readonly bool $normalizeToNfc;

    public function __construct(
        bool $tehMarbuta = false,
        bool $foldHamza = false,
        bool $stripTashkeel = false,
        bool $stripKashida = false,
        bool $stripBidiMarks = false,
        bool $normalizeToNfc = false,
    ) {
        if ($normalizeToNfc && !class_exists(\Normalizer::class)) {
            throw EnvironmentException::missing(
                ErrorCode::ENV_MISSING_EXT_INTL,
                'CharNormalizer::$normalizeToNfc requires ext-intl. Install the intl extension or unset the flag.',
            );
        }

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

        if ($foldHamza) {
            // آ (alef-madda, U+0622) is preserved — semantically distinct.
            foreach (["\u{0623}" => "\u{0627}", "\u{0625}" => "\u{0627}", "\u{0624}" => "\u{0648}", "\u{0626}" => "\u{06CC}"] as $from => $to) {
                $this->search[]  = $from;
                $this->replace[] = $to;
            }
        }

        $this->stripTashkeel  = $stripTashkeel;
        $this->stripKashida   = $stripKashida;
        $this->stripBidiMarks = $stripBidiMarks;
        $this->normalizeToNfc = $normalizeToNfc;
    }

    public function normalize(string $text): string
    {
        $text = str_replace($this->search, $this->replace, $text);

        if ($this->stripTashkeel) {
            $text = (string) preg_replace('/[\x{064B}-\x{065F}]/u', '', $text);
        }

        if ($this->stripKashida) {
            $text = str_replace("\u{0640}", '', $text);
        }

        if ($this->stripBidiMarks) {
            // U+200D ZWJ and U+FEFF BOM travel in alongside the bidi controls
            // when text is copied from Word / Office, so folded into one pass.
            $text = (string) preg_replace('/[\x{200D}\x{200E}\x{200F}\x{202A}-\x{202E}\x{FEFF}]/u', '', $text);
        }

        if ($this->normalizeToNfc) {
            $normalized = \Normalizer::normalize($text, \Normalizer::FORM_C);
            if ($normalized !== false) {
                $text = $normalized;
            }
        }

        return $text;
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
