<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Fixtures;

use Eram\Abzar\Digits\DigitConverter;
use Eram\Abzar\Format\NumberFormatter;

/**
 * addCommas.spec.ts, removeCommas.spec.ts and digits.spec.ts.
 *
 * | upstream                     | abzar                                     |
 * |------------------------------|-------------------------------------------|
 * | addCommas(n)                 | NumberFormatter::withSeparators($n)       |
 * | removeCommas(s)              | NumberFormatter::withSeparators($s, '')   |
 * | digitsArToFa / digitsEnToFa  | DigitConverter::toPersian($s)             |
 * | digitsArToEn / digitsFaToEn  | DigitConverter::toEnglish($s)             |
 * | digitsEnToAr / digitsFaToAr  | DigitConverter::toArabic($s)              |
 *
 * Upstream's "must be string" TypeError cases have no PHP counterpart; the
 * signatures are typed.
 */
final class FormattingContractTest extends PersianToolsFixtureTestCase
{
    /**
     * @dataProvider addCommasVectors
     */
    public function test_add_commas_parity(int|float|string $input, string $upstream): void
    {
        self::assertSame($upstream, NumberFormatter::withSeparators($input));
    }

    /** @return iterable<string, array{int|float|string, string}> */
    public static function addCommasVectors(): iterable
    {
        yield 'addCommas.spec.ts:6'  => [30_000_000, '30,000,000'];
        yield 'addCommas.spec.ts:7'  => ['30000000', '30,000,000'];
        yield 'addCommas.spec.ts:8'  => ['30,000,000', '30,000,000'];
        yield 'addCommas.spec.ts:9'  => ['۳۰۰۰۰۰۰۰', '30,000,000'];
        yield 'addCommas.spec.ts:12' => ['30,000,000.02', '30,000,000.02'];
        yield 'addCommas.spec.ts:13' => ['12500.9', '12,500.9'];
        yield 'addCommas.spec.ts:14' => [12500.9, '12,500.9'];
        yield 'addCommas.spec.ts:15' => ['51000.123456789', '51,000.123456789'];
        yield 'addCommas.spec.ts:17' => [300, '300'];
        yield 'addCommas.spec.ts:22' => [0, '0'];
    }

    /**
     * @dataProvider digitVectors
     */
    public function test_digits_parity(string $upstreamFn, string $input, string $upstream): void
    {
        self::assertSame($upstream, self::abzar($upstreamFn, $input));
    }

    /** @return iterable<string, array{string, string, string}> */
    public static function digitVectors(): iterable
    {
        yield 'digits.spec.ts:6'   => ['digitsArToFa', '٠١٢٣٤٥٦٧٨٩', '۰۱۲۳۴۵۶۷۸۹'];
        yield 'digits.spec.ts:14'  => ['digitsArToFa', '', ''];
        yield 'digits.spec.ts:15'  => ['digitsArToFa', 'Text ٠١٢٣٤٥٦٧٨٩', 'Text ۰۱۲۳۴۵۶۷۸۹'];
        yield 'digits.spec.ts:19'  => ['digitsArToEn', '٠١٢٣٤٥٦٧٨٩', '0123456789'];
        yield 'digits.spec.ts:20'  => ['digitsArToEn', '89١٢٣4٥', '8912345'];
        yield 'digits.spec.ts:30'  => ['digitsArToEn', 'Text ٠١٢٣٤٥٦٧٨٩', 'Text 0123456789'];
        yield 'digits.spec.ts:34'  => ['digitsEnToFa', '123۴۵۶', '۱۲۳۴۵۶'];
        yield 'digits.spec.ts:35'  => ['digitsEnToFa', '123', '۱۲۳'];
        yield 'digits.spec.ts:36'  => ['digitsEnToFa', '1234567891', '۱۲۳۴۵۶۷۸۹۱'];
        yield 'digits.spec.ts:37'  => ['digitsEnToFa', '0', '۰'];
        yield 'digits.spec.ts:56'  => ['digitsEnToAr', '123456', '١٢٣٤٥٦'];
        yield 'digits.spec.ts:57'  => ['digitsEnToAr', '1234567891', '١٢٣٤٥٦٧٨٩١'];
        yield 'digits.spec.ts:58'  => ['digitsEnToAr', '0', '٠'];
        yield 'digits.spec.ts:59'  => ['digitsEnToAr', '123٤٥٦', '١٢٣٤٥٦'];
        yield 'digits.spec.ts:76'  => ['digitsFaToEn', '123۴۵۶', '123456'];
        yield 'digits.spec.ts:77'  => ['digitsFaToEn', '۸۹123۴۵', '8912345'];
        yield 'digits.spec.ts:78'  => ['digitsFaToEn', '۰۱۲۳۴۵۶۷۸۹', '0123456789'];
        yield 'digits.spec.ts:88'  => ['digitsFaToAr', '۰۱۲۳۴۵۶۷۸۹', '٠١٢٣٤٥٦٧٨٩'];
        yield 'digits.spec.ts:89'  => ['digitsFaToAr', '۱۷۸۲۳۴۰۵۶۹', '١٧٨٢٣٤٠٥٦٩'];
        yield 'digits.spec.ts:90'  => ['digitsFaToAr', '۷۸٤۲۳٤۴', '٧٨٤٢٣٤٤'];
        // :101-106 chain two converters (digitsFaToEn(digitsArToFa(s)) etc.);
        // abzar's target converter does it in one call.
        yield 'digits.spec.ts:102' => ['digitsFaToEn', '٤٤٤444۴۴۴', '444444444'];
        yield 'digits.spec.ts:103' => ['digitsFaToEn', '٠١٢٣٤٥٦٧٨٩', '0123456789'];
        yield 'digits.spec.ts:104' => ['digitsEnToFa', '٠١٢٣٤٥٦٧٨٩', '۰۱۲۳۴۵۶۷۸۹'];
        yield 'digits.spec.ts:105' => ['digitsEnToFa', 'Text ٠١٢٣٤٥٦٧٨٩', 'Text ۰۱۲۳۴۵۶۷۸۹'];
    }

    /**
     * @dataProvider divergences
     */
    public function test_divergence(string $api, string $input, mixed $upstream, mixed $abzar, string $reason): void
    {
        self::assertDivergence($upstream, $abzar, self::outcome(static fn () => self::abzar($api, $input)), $reason);
    }

    /** @return iterable<string, array{string, string, mixed, mixed, string}> */
    public static function divergences(): iterable
    {
        $removeCommas = 'abzar has no removeCommas; withSeparators($s, \'\') strips the grouping and returns a string, '
            . 'so decimals and values past 2^53 stay exact. Cast it if you need a number.';
        yield 'removeCommas.spec.ts:5' => ['removeCommas', '30,000,000', 30_000_000, '30000000', $removeCommas];
        yield 'removeCommas.spec.ts:6' => ['removeCommas', '300', 300, '300', $removeCommas];

        $byTarget = 'DigitConverter is keyed by target script and folds every other digit set into it, '
            . 'where upstream converts one source script per function.';
        yield 'digits.spec.ts:7'  => ['digitsArToFa', '۸۹123۴۵', '۸۹123۴۵', '۸۹۱۲۳۴۵', $byTarget];
        yield 'digits.spec.ts:21' => ['digitsArToEn', '0123۴۵۶789', '0123۴۵۶789', '0123456789', $byTarget];
        yield 'digits.spec.ts:38' => ['digitsEnToFa', '٤٥٦', '٤٥٦', '۴۵۶', $byTarget];
        yield 'digits.spec.ts:91' => ['digitsFaToAr', '٤٤٤444۴۴۴', '٤٤٤444٤٤٤', '٤٤٤٤٤٤٤٤٤', $byTarget];
    }

    private static function abzar(string $upstreamFn, string $input): string
    {
        return match ($upstreamFn) {
            'removeCommas'                 => NumberFormatter::withSeparators($input, ''),
            'digitsArToFa', 'digitsEnToFa' => DigitConverter::toPersian($input),
            'digitsArToEn', 'digitsFaToEn' => DigitConverter::toEnglish($input),
            'digitsEnToAr', 'digitsFaToAr' => DigitConverter::toArabic($input),
            default                        => throw new \InvalidArgumentException("No abzar counterpart mapped for $upstreamFn"),
        };
    }
}
