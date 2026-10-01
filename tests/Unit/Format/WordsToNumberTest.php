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

    public function test_decimal_with_leading_zero(): void
    {
        self::assertSame(3.05, WordsToNumber::parse('سه ممیز صفر پنج'));
    }

    public function test_decimal_zero_integer_with_leading_zero(): void
    {
        self::assertSame(0.05, WordsToNumber::parse('صفر ممیز صفر پنج'));
    }

    public function test_quintillion(): void
    {
        self::assertSame(1_000_000_000_000_000_000, WordsToNumber::parse('یک کوینتیلیون'));
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
        foreach ([0, 1, 12, 99, 100, 345, 1234, 1_000_000, 1_000_000_000_000_000_000] as $n) {
            self::assertSame($n, WordsToNumber::parse(NumberToWords::convert($n)), "roundtrip failed for $n");
        }
    }

    public function test_decimal_roundtrips_with_number_to_words(): void
    {
        foreach ([3.05, 0.05, 3.005, 3.025] as $n) {
            self::assertSame($n, WordsToNumber::parse(NumberToWords::convert($n)), "decimal roundtrip failed for $n");
        }
    }

    public function test_values_past_php_int_max_return_null(): void
    {
        // B7 — used to escape as a TypeError from the int-typed accumulator.
        self::assertNull(WordsToNumber::parse('ده کوینتیلیون'));
        self::assertNull(WordsToNumber::parse('نهصد کوینتیلیون'));
        self::assertNull(WordsToNumber::parse('منفی ده کوینتیلیون'));
        self::assertSame(9_000_000_000_000_000_000, WordsToNumber::parse('نه کوینتیلیون'));
    }

    /**
     * B7 — sequences no Persian speaker would produce must not silently sum.
     *
     * @return iterable<string, array{string}>
     */
    public static function nonsense(): iterable
    {
        yield 'adjacent ones'        => ['دو سه'];
        yield 'adjacent tens'        => ['بیست سی'];
        yield 'teen then ones'       => ['یازده دو'];
        yield 'tens then teen'       => ['بیست یازده'];
        yield 'ones then tens'       => ['دو بیست'];
        yield 'adjacent hundreds'    => ['یکصد دویست'];
        yield 'repeated scale'       => ['یک میلیون دو میلیون'];
        yield 'ascending scale'      => ['یک میلیون یک میلیارد'];
        yield 'double thousand'      => ['دو هزار سه هزار'];
        yield 'nonsense fraction'    => ['سه ممیز دو سه'];
    }

    /**
     * @dataProvider nonsense
     */
    public function test_nonsense_sequences_return_null(string $input): void
    {
        self::assertNull(WordsToNumber::parse($input));
    }

    /**
     * @return iterable<string, array{string, int}>
     */
    public static function colloquial(): iterable
    {
        // Shapes from persian-tools' wordsToNumber.spec.ts and everyday writing.
        yield 'tens-ones without va'  => ['دوازده هزار بیست دو', 12022];
        yield 'all without va'        => ['نهصد نود نه هزار نهصد نود نه', 999999];
        yield 'hundreds then thousand' => ['چهارصد پنجاه هزار', 450000];
        yield 'bare scale'            => ['میلیون', 1_000_000];
        yield 'thousand billion'      => ['هزار میلیارد', 1_000_000_000_000];
        yield 'split hundred'         => ['سه صد', 300];
        yield 'one split hundred'     => ['یک صد و بیست', 120];
    }

    /**
     * @dataProvider colloquial
     */
    public function test_colloquial_forms(string $input, int $expected): void
    {
        self::assertSame($expected, WordsToNumber::parse($input));
    }

    public function test_large_scales_accumulate(): void
    {
        self::assertSame(1_002_000_000, WordsToNumber::parse('یک میلیارد و دو میلیون'));
        self::assertSame(3_000_000_000_004, WordsToNumber::parse('سه تریلیون و چهار'));
        self::assertSame(100, WordsToNumber::parse('صد'));
        self::assertSame(120, WordsToNumber::parse('صد و بیست'));
    }

    public function test_surrounding_whitespace_is_ignored(): void
    {
        self::assertSame(-3, WordsToNumber::parse('  منفی سه  '));
        // The sign strip leaves a leading space on the integer part.
        self::assertSame(0, WordsToNumber::parse('منفی  صفر'));
    }

    public function test_second_decimal_separator_returns_null(): void
    {
        self::assertNull(WordsToNumber::parse('سه ممیز پنج ممیز دو'));
    }

    public function test_fraction_zero_padding_survives_empty_tokens(): void
    {
        // ZWNJ followed by a space splits into an empty token between the zeros.
        self::assertSame(3.005, WordsToNumber::parse("سه ممیز صفر\u{200C} صفر پنج"));
    }

    public function test_bare_hundred_after_thousand(): void
    {
        self::assertSame(1100, WordsToNumber::parse('هزار صد'));
        self::assertSame(1100, WordsToNumber::parse('یک هزار و صد'));
    }

    public function test_quadrillion_scale(): void
    {
        self::assertSame(1_000_000_000_000_000, WordsToNumber::parse('یک کوادریلیون'));
        self::assertSame(2_000_000_000_000_000, WordsToNumber::parse('دو کوادریلیون'));
    }

    public function test_php_int_max_boundary(): void
    {
        self::assertSame(PHP_INT_MAX, WordsToNumber::parse(NumberToWords::convert(PHP_INT_MAX)));
        // PHP_INT_MAX + 1 (…۸۰۸) overflows in the trailing sub-million group.
        $maxPlusOne = preg_replace('/هفت$/u', 'هشت', NumberToWords::convert(PHP_INT_MAX));
        self::assertStringEndsWith('هشتصد و هشت', (string) $maxPlusOne);
        self::assertNull(WordsToNumber::parse((string) $maxPlusOne));
        // Overflow while adding a second big-scale group.
        self::assertNull(WordsToNumber::parse('نه کوینتیلیون و نهصد کوادریلیون'));
    }

    /**
     * Spellings persian-tools reads (and NumberToWords never emits) that map
     * to exactly one value.
     *
     * @return iterable<string, array{string, int}>
     */
    public static function alternateSpellings(): iterable
    {
        yield 'colloquial six'          => ['شیش', 6];
        yield 'colloquial four hundred' => ['چارصد', 400];
        yield 'billion as billion'      => ['بیلیون', 1_000_000_000];
        yield 'quadrillion with alef madda' => ['کوآدریلیون', 1_000_000_000_000_000];
        yield 'in a phrase'             => ['منفی چارصد و شیش', -406];
        yield 'mixed scales'            => ['دو بیلیون و شیش میلیون', 2_006_000_000];
    }

    /**
     * @dataProvider alternateSpellings
     */
    public function test_alternate_spellings(string $input, int $expected): void
    {
        self::assertSame($expected, WordsToNumber::parse($input));
    }

    public function test_billion_and_milliard_are_the_same_scale(): void
    {
        // Both name 10⁹, so they can't appear in descending order together.
        self::assertNull(WordsToNumber::parse('یک بیلیون یک میلیارد'));
    }
}
