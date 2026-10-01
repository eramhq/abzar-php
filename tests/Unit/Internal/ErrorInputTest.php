<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Internal;

use Eram\Abzar\Internal\ErrorInput;
use PHPUnit\Framework\TestCase;

final class ErrorInputTest extends TestCase
{
    /**
     * Invisible and look-alike characters that ride along when IDs are pasted
     * from phones, RTL chat apps, PDFs, and word processors.
     *
     * @return iterable<string, array{string}>
     */
    public static function pastedNoise(): iterable
    {
        yield 'NBSP'               => ["\u{00A0}"];
        yield 'narrow NBSP'        => ["\u{202F}"];
        yield 'ZWNJ'               => ["\u{200C}"];
        yield 'LRM'                => ["\u{200E}"];
        yield 'RLM'                => ["\u{200F}"];
        yield 'Arabic letter mark' => ["\u{061C}"];
        yield 'hyphen'             => ["\u{2010}"];
        yield 'en dash'            => ["\u{2013}"];
        yield 'em dash'            => ["\u{2014}"];
        yield 'minus sign'         => ["\u{2212}"];
        yield 'BOM'                => ["\u{FEFF}"];
    }

    /**
     * @dataProvider pastedNoise
     */
    public function test_digits_strips_pasted_noise(string $noise): void
    {
        self::assertSame('0013542419', ErrorInput::digits($noise . '001' . $noise . '354' . $noise . '2419' . $noise));
    }

    public function test_digits_folds_persian_and_arabic_digits(): void
    {
        self::assertSame('0123456789', ErrorInput::digits('۰۱۲۳۴٥٦٧٨٩'));
    }

    public function test_digits_keeps_letters(): void
    {
        self::assertSame('12ب345', ErrorInput::digits('12 ب 345'));
    }

    public function test_extra_char_class_is_escaped(): void
    {
        // Regex metacharacters in $extraCharClass must be taken literally;
        // an unescaped "]" or "/" used to break the pattern and return ''.
        self::assertSame('0212345678', ErrorInput::digits('[021]/2345678', '[]/'));
        self::assertSame('a^b', ErrorInput::digits('a^b', '()'));
    }

    public function test_truncate_strips_control_chars(): void
    {
        self::assertSame('ab', ErrorInput::truncate("a\x00b", 10));
        self::assertSame('abc…', ErrorInput::truncate('abcdef', 3));
    }

    public function test_digits_survives_invalid_utf8(): void
    {
        // The /u pattern rejects malformed UTF-8; the ASCII fallback must still
        // strip spaces and dashes rather than collapsing the input to ''.
        self::assertSame("12\xFF34", ErrorInput::digits("12 \xFF-34"));
    }
}
