<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Fixtures;

use Eram\Abzar\Format\NumberToWords;
use Eram\Abzar\Format\OrdinalNumber;
use Eram\Abzar\Format\WordsToNumber;

/**
 * NumberToWords.spec.ts, addOrdinalSuffix.spec.ts and wordsToNumber.spec.ts.
 *
 * | upstream                          | abzar                          |
 * |-----------------------------------|--------------------------------|
 * | numberToWords(n)                  | NumberToWords::convert($n)     |
 * | numberToWords(n, {ordinal: true}) | OrdinalNumber::toWord($n)      |
 * | addOrdinalSuffix(s)               | OrdinalNumber::addSuffix($s)   |
 * | wordsToNumber(s)                  | WordsToNumber::parse($s)       |
 *
 * Out of scope: the digits / addCommas output options (pipe the result through
 * DigitConverter / NumberFormatter instead) and the fuzzy parser
 * (wordsToNumber-fuzzy.spec.ts, moneyWordsToNumber.spec.ts).
 */
final class NumberWordsContractTest extends PersianToolsFixtureTestCase
{
    /**
     * @dataProvider numberToWordsVectors
     */
    public function test_number_to_words_parity(int $n, string $upstream): void
    {
        self::assertSame($upstream, NumberToWords::convert($n));
    }

    /** @return iterable<string, array{int, string}> */
    public static function numberToWordsVectors(): iterable
    {
        yield 'NumberToWords.spec.ts:5'  => [4, 'چهار'];
        yield 'NumberToWords.spec.ts:6'  => [33, 'سی و سه'];
        // Upstream passes the string "8,356".
        yield 'NumberToWords.spec.ts:7'  => [8356, 'هشت هزار و سیصد و پنجاه و شش'];
        // Upstream only asserts the length (5).
        yield 'NumberToWords.spec.ts:9'  => [500, 'پانصد'];
        yield 'NumberToWords.spec.ts:10' => [30_000_000_000, 'سی میلیارد'];
        yield 'NumberToWords.spec.ts:19' => [0, 'صفر'];
        yield 'NumberToWords.spec.ts:28' => [1000, 'یک هزار'];
        yield 'NumberToWords.spec.ts:29' => [12345, 'دوازده هزار و سیصد و چهل و پنج'];
    }

    /**
     * Every cardinal upstream writes, including the spellings abzar doesn't
     * emit itself, parses back to the same number.
     *
     * @dataProvider upstreamCardinals
     */
    public function test_words_to_number_reads_upstream_spelling(int $n, string $upstream): void
    {
        self::assertSame($n, WordsToNumber::parse($upstream));
    }

    /** @return iterable<string, array{int, string}> */
    public static function upstreamCardinals(): iterable
    {
        yield from self::numberToWordsVectors();
        yield 'NumberToWords.spec.ts:8'  => [500_443, 'پانصد هزار و چهار صد و چهل و سه'];
        yield 'NumberToWords.spec.ts:11' => [987_654_321, 'نه صد و هشتاد و هفت میلیون و شش صد و پنجاه و چهار هزار و سیصد و بیست و یک'];
        yield 'NumberToWords.spec.ts:22' => [9_006_199_254_740_992, 'نه کوآدریلیون و شش تریلیون و صد و نود و نه میلیارد و دویست و پنجاه و چهار میلیون و هفت صد و چهل هزار و نه صد و نود و دو'];
    }

    /**
     * @dataProvider ordinalVectors
     */
    public function test_ordinal_parity(int $n, string $upstream): void
    {
        self::assertSame($upstream, OrdinalNumber::toWord($n));
    }

    /** @return iterable<string, array{int, string}> */
    public static function ordinalVectors(): iterable
    {
        yield 'NumberToWords.spec.ts:17' => [33, 'سی و سوم'];
        yield 'NumberToWords.spec.ts:18' => [45, 'چهل و پنجم'];
    }

    /**
     * @dataProvider addOrdinalSuffixVectors
     */
    public function test_add_ordinal_suffix_parity(string $word, string $upstream): void
    {
        self::assertSame($upstream, OrdinalNumber::addSuffix($word));
    }

    /** @return iterable<string, array{string, string}> */
    public static function addOrdinalSuffixVectors(): iterable
    {
        yield 'addOrdinalSuffix.spec.ts:6' => ['چهل و سه', 'چهل و سوم'];
        yield 'addOrdinalSuffix.spec.ts:7' => ['چهل و پنج', 'چهل و پنجم'];
    }

    /**
     * @dataProvider wordsToNumberVectors
     */
    public function test_words_to_number_parity(string $words, int $upstream): void
    {
        self::assertSame($upstream, WordsToNumber::parse($words));
    }

    /** @return iterable<string, array{string, int}> */
    public static function wordsToNumberVectors(): iterable
    {
        yield 'wordsToNumber.spec.ts:18'  => ['منفی سه هزار', -3000];
        yield 'wordsToNumber.spec.ts:21'  => ['سه هزار دویست و دوازده', 3212];
        yield 'wordsToNumber.spec.ts:24'  => ['دوازده هزار بیست دو', 12022];
        // :30 asserts the addCommas string "12,022".
        yield 'wordsToNumber.spec.ts:30'  => ['دوازده هزار و بیست و دو', 12022];
        // :53 and :56 assert Arabic-digit strings.
        yield 'wordsToNumber.spec.ts:56'  => ['چهارصد پنجاه هزار', 450_000];
        yield 'wordsToNumber.spec.ts:150' => ['یک میلیون و سی هزار', 1_030_000];
        yield 'wordsToNumber.spec.ts:157' => ['منفی یک میلیون و سی هزار', -1_030_000];
        yield 'wordsToNumber.spec.ts:179' => ['منفی صفر', 0];
        yield 'wordsToNumber.spec.ts:193' => ['نهصد نود نه هزار نهصد نود نه', 999_999];
        // :418 runs with autoConvertArabicCharsToPersian; the Persian-keyboard
        // form of "منفی چارصد یك" is used here.
        yield 'wordsToNumber.spec.ts:418' => ['منفی چارصد یک', -401];
    }

    /**
     * wordsToNumber.spec.ts:106-137 iterate the UNITS / TEN / MAGNITUDE maps
     * from upstream src/modules/wordsToNumber/constants.ts (lines 4-62 at the
     * pinned SHA), which isn't vendored; the entries are copied here.
     *
     * @dataProvider wordsToNumberTables
     */
    public function test_words_to_number_tables(string $word, int $upstream): void
    {
        self::assertSame($upstream, WordsToNumber::parse($word));
    }

    /** @return iterable<string, array{string, int}> */
    public static function wordsToNumberTables(): iterable
    {
        $units = [
            'صفر' => 0, 'یک' => 1, 'دو' => 2, 'سه' => 3, 'چهار' => 4, 'پنج' => 5, 'شش' => 6, 'شیش' => 6,
            'هفت' => 7, 'هشت' => 8, 'نه' => 9, 'ده' => 10, 'یازده' => 11, 'دوازده' => 12, 'سیزده' => 13,
            'چهارده' => 14, 'پانزده' => 15, 'شانزده' => 16, 'هفده' => 17, 'هجده' => 18, 'نوزده' => 19,
            'بیست' => 20, 'سی' => 30, 'چهل' => 40, 'پنجاه' => 50, 'شصت' => 60, 'هفتاد' => 70, 'هشتاد' => 80,
            'نود' => 90,
        ];
        $ten = [
            'صد' => 100, 'یکصد' => 100, "یک\u{200C}صد" => 100, 'دویست' => 200, 'سیصد' => 300, 'چهارصد' => 400,
            'پانصد' => 500, 'ششصد' => 600, 'هفتصد' => 700, 'هشتصد' => 800, 'نهصد' => 900,
        ];
        $magnitude = [
            'هزار' => 1000, 'میلیون' => 1_000_000, 'بیلیون' => 1_000_000_000, 'میلیارد' => 1_000_000_000,
            'تریلیون' => 1_000_000_000_000,
        ];

        foreach (['UNITS' => $units, 'TEN' => $ten, 'MAGNITUDE' => $magnitude] as $table => $entries) {
            foreach ($entries as $word => $value) {
                yield "$table $word" => [(string) $word, $value];
            }
        }
    }

    /**
     * @dataProvider divergences
     */
    public function test_divergence(string $api, int|string $input, mixed $upstream, mixed $abzar, string $reason): void
    {
        self::assertDivergence($upstream, $abzar, self::abzar($api, $input), $reason);
    }

    /** @return iterable<string, array{string, int|string, mixed, mixed, string}> */
    public static function divergences(): iterable
    {
        $spelling = 'abzar joins the hundreds (چهارصد, نهصد), writes یکصد for a bare hundred and spells 10^15 کوادریلیون; '
            . 'WordsToNumber reads both spellings.';

        yield 'NumberToWords.spec.ts:8' => [
            'numberToWords', 500_443,
            'پانصد هزار و چهار صد و چهل و سه',
            'پانصد هزار و چهارصد و چهل و سه',
            $spelling,
        ];
        yield 'NumberToWords.spec.ts:11' => [
            'numberToWords', 987_654_321,
            'نه صد و هشتاد و هفت میلیون و شش صد و پنجاه و چهار هزار و سیصد و بیست و یک',
            'نهصد و هشتاد و هفت میلیون و ششصد و پنجاه و چهار هزار و سیصد و بیست و یک',
            $spelling,
        ];
        yield 'NumberToWords.spec.ts:22' => [
            'numberToWords', 9_006_199_254_740_992,
            'نه کوآدریلیون و شش تریلیون و صد و نود و نه میلیارد و دویست و پنجاه و چهار میلیون و هفت صد و چهل هزار و نه صد و نود و دو',
            'نه کوادریلیون و شش تریلیون و یکصد و نود و نه میلیارد و دویست و پنجاه و چهار میلیون و هفتصد و چهل هزار و نهصد و نود و دو',
            $spelling,
        ];
        yield 'NumberToWords.spec.ts:14' => [
            'numberToWords(ordinal)', 500_443,
            'پانصد هزار و چهار صد و چهل و سوم',
            'پانصد هزار و چهارصد و چهل و سوم',
            $spelling,
        ];
        yield 'NumberToWords.spec.ts:15' => [
            'numberToWords(ordinal)', -30,
            'منفی سی اُم',
            'throws ORDINAL_NUMBER.NON_POSITIVE',
            'An ordinal names a position, so abzar rejects n < 1 instead of prefixing منفی.',
        ];
        yield 'NumberToWords.spec.ts:16' => [
            'numberToWords(ordinal)', -123,
            'منفی صد و بیست و سوم',
            'throws ORDINAL_NUMBER.NON_POSITIVE',
            'An ordinal names a position, so abzar rejects n < 1 instead of prefixing منفی.',
        ];
        yield 'addOrdinalSuffix.spec.ts:8' => [
            'addOrdinalSuffix', 'سی',
            'سی اُم',
            "سی\u{200C}ام",
            'Words ending in ی take ام joined with a ZWNJ (سی‌ام), per standard orthography; no space, no damma.',
        ];

        $lenient = 'abzar parses number words only and returns null for anything else rather than guessing.';
        yield 'wordsToNumber.spec.ts:64' => ['wordsToNumber', 'منفی ۳ هزار', -3000, null, $lenient . ' Mixed digits and words are rejected.'];
        yield 'wordsToNumber.spec.ts:67' => ['wordsToNumber', 'منفی 3 هزار و 200', -3200, null, $lenient . ' Mixed digits and words are rejected.'];
        yield 'wordsToNumber.spec.ts:165' => ['wordsToNumber', 'منفی چهارصد 200', -600, null, $lenient . ' Mixed digits and words are rejected.'];
        yield 'wordsToNumber.spec.ts:172' => ['wordsToNumber', '0', 0, null, $lenient . ' Digit strings belong to DigitConverter / (int).'];
        yield 'wordsToNumber.spec.ts:186' => ['wordsToNumber', '-999', -999, null, $lenient . ' Digit strings belong to DigitConverter / (int).'];
        yield 'wordsToNumber.spec.ts:76' => ['wordsToNumber', 'منفی سه هزارمین', -3000, null, $lenient . ' Ordinal words are not cardinals.'];
        yield 'wordsToNumber.spec.ts:79' => ['wordsToNumber', 'منفی سه هزارم', -3000, null, $lenient . ' Ordinal words are not cardinals.'];
        yield 'wordsToNumber.spec.ts:88' => ['wordsToNumber', 'منفی سی اُم', -30, null, $lenient . ' Ordinal words are not cardinals.'];
        yield 'wordsToNumber.spec.ts:201' => ['wordsToNumber', 'دهم هزار', 10000, null, $lenient . ' Ordinal words are not cardinals.'];
        yield 'wordsToNumber.spec.ts:208' => ['wordsToNumber', 'سلام دنیا', 0, null, $lenient . ' Non-number text is null, not 0.'];
        yield 'wordsToNumber.spec.ts:215' => ['wordsToNumber', 'منفی سلام دنیا', 0, null, $lenient . ' Non-number text is null, not 0.'];
        yield 'wordsToNumber.spec.ts:96' => ['wordsToNumber', '', '', null, $lenient . ' Empty input is null, not an empty string.'];
    }

    private static function abzar(string $api, int|string $input): mixed
    {
        return self::outcome(match ($api) {
            'numberToWords'          => static fn () => NumberToWords::convert((int) $input),
            'numberToWords(ordinal)' => static fn () => OrdinalNumber::toWord((int) $input),
            'addOrdinalSuffix'       => static fn () => OrdinalNumber::addSuffix((string) $input),
            'wordsToNumber'          => static fn () => WordsToNumber::parse((string) $input),
            default                  => throw new \InvalidArgumentException("No abzar counterpart mapped for $api"),
        });
    }
}
