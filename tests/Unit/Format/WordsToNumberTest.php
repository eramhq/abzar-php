<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Format;

use Eram\Abzar\Format\NumberToWords;
use Eram\Abzar\Format\WordsToNumber;
use PHPUnit\Framework\TestCase;

final class WordsToNumberTest extends TestCase
{
    /**
     * @return iterable<string, array{string, int}>
     */
    public static function cases(): iterable
    {
        yield 'zero'       => ['صفر', 0];
        yield 'one'        => ['یک', 1];
        yield 'ten'        => ['ده', 10];
        yield 'eleven'     => ['یازده', 11];
        yield 'twenty-one' => ['بیست و یک', 21];
        yield 'hundred'    => ['یکصد', 100];
        yield 'tens-ones'  => ['سی و چهار', 34];
        yield 'thousand'   => ['یک هزار', 1000];
        yield 'k-and-ones' => ['یک هزار و دویست و سی و چهار', 1234];
        yield 'million'    => ['دو میلیون', 2_000_000];
        yield 'big'        => ['سه میلیون و چهارصد و پنجاه و شش هزار و هفت', 3_456_007];
    }

    /**
     * @dataProvider cases
     */
    public function test_parse(string $input, int $expected): void
    {
        self::assertSame($expected, WordsToNumber::parse($input));
    }

    public function test_negative(): void
    {
        self::assertSame(-5, WordsToNumber::parse('منفی پنج'));
    }

    public function test_decimal(): void
    {
        self::assertSame(3.5, WordsToNumber::parse('سه ممیز پنج'));
    }

    public function test_unknown_token_returns_null(): void
    {
        self::assertNull(WordsToNumber::parse('foo bar'));
    }

    public function test_empty_returns_null(): void
    {
        self::assertNull(WordsToNumber::parse(''));
        self::assertNull(WordsToNumber::parse('   '));
    }

    public function test_mixed_digits_returns_null(): void
    {
        self::assertNull(WordsToNumber::parse('یک هزار و 200'));
    }

    /**
     * Inverts {@see NumberToWords::convert()} — for every integer that
     * round-trips cleanly, parse(convert($n)) must equal $n.
     */
    public function test_roundtrips_with_number_to_words(): void
    {
        foreach ([0, 1, 12, 99, 100, 345, 1234, 1_000_000] as $n) {
            self::assertSame($n, WordsToNumber::parse(NumberToWords::convert($n)), "roundtrip failed for $n");
        }
    }
}
