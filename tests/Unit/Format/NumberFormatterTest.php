<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Format;

use Eram\Abzar\Format\NumberFormatter;
use PHPUnit\Framework\TestCase;

class NumberFormatterTest extends TestCase
{
    public function test_basic_thousands(): void
    {
        $this->assertSame('1,234,567', NumberFormatter::withSeparators(1234567));
    }

    public function test_small_number_unchanged(): void
    {
        $this->assertSame('999', NumberFormatter::withSeparators(999));
    }

    public function test_zero(): void
    {
        $this->assertSame('0', NumberFormatter::withSeparators(0));
    }

    public function test_single_digit(): void
    {
        $this->assertSame('5', NumberFormatter::withSeparators(5));
    }

    public function test_negative(): void
    {
        $this->assertSame('-1,234,567', NumberFormatter::withSeparators(-1234567));
    }

    public function test_decimal_preserved(): void
    {
        $this->assertSame('30,000,000.02', NumberFormatter::withSeparators(30000000.02));
    }

    public function test_custom_separator(): void
    {
        $this->assertSame('1٬234٬567', NumberFormatter::withSeparators(1234567, '٬'));
    }

    public function test_string_input(): void
    {
        $this->assertSame('1,234,567', NumberFormatter::withSeparators('1234567'));
    }

    public function test_persian_digit_input(): void
    {
        $this->assertSame('1,234,567', NumberFormatter::withSeparators('۱۲۳۴۵۶۷'));
    }

    public function test_idempotent_already_formatted(): void
    {
        $this->assertSame('1,234,567', NumberFormatter::withSeparators('1,234,567'));
    }

    public function test_large_string_number(): void
    {
        $this->assertSame(
            '999,999,999,999,999',
            NumberFormatter::withSeparators('999999999999999')
        );
    }

    public function test_string_decimal(): void
    {
        $this->assertSame('1,234.56', NumberFormatter::withSeparators('1234.56'));
    }

    public function test_invalid_string_throws(): void
    {
        $this->expectException(\Eram\Abzar\Exception\FormatException::class);
        NumberFormatter::withSeparators('abc');
    }

    /**
     * B5 — Persian separators, a leading "+" and grouped spacing are all
     * shapes this library itself emits or users paste.
     *
     * @return iterable<string, array{string, string}>
     */
    public static function persianShapedInputs(): iterable
    {
        yield 'arabic comma separator'     => ['50،000', '50,000'];
        yield 'arabic thousands separator' => ['۵۰٬۰۰۰', '50,000'];
        yield 'arabic decimal separator'   => ['۱۲۳۴٫۵', '1,234.5'];
        yield 'leading plus'               => ['+1000', '1,000'];
        yield 'inner spaces'               => ['1 000 000', '1,000,000'];
        yield 'nbsp grouping'              => ["1\u{00A0}000\u{202F}000", '1,000,000'];
    }

    /**
     * @dataProvider persianShapedInputs
     */
    public function test_accepts_persian_shaped_input(string $input, string $expected): void
    {
        $this->assertSame($expected, NumberFormatter::withSeparators($input));
    }

    public function test_round_trips_currency_output(): void
    {
        $formatted = \Eram\Abzar\Money\Currency::format(50000, withUnit: false);
        $this->assertSame('50,000', NumberFormatter::withSeparators($formatted));
    }

    public function test_plus_is_only_accepted_as_a_sign(): void
    {
        $this->expectException(\Eram\Abzar\Exception\FormatException::class);
        NumberFormatter::withSeparators('1+000');
    }
}
