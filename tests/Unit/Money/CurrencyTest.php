<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Money;

use Eram\Abzar\Money\Currency;
use Eram\Abzar\Money\Unit;
use PHPUnit\Framework\TestCase;

final class CurrencyTest extends TestCase
{
    public function test_format_default_toman_persian_digits(): void
    {
        self::assertSame('۱،۲۳۴ تومان', Currency::format(1234));
    }

    public function test_format_rial_ascii_digits(): void
    {
        self::assertSame(
            '12,340 ریال',
            Currency::format(12340, Unit::RIAL, persianDigits: false, separator: ','),
        );
    }

    public function test_format_without_unit(): void
    {
        self::assertSame('۱،۰۰۰', Currency::format(1000, withUnit: false));
    }

    public function test_format_zero(): void
    {
        self::assertSame('۰ تومان', Currency::format(0));
    }

    public function test_format_negative(): void
    {
        self::assertSame('-۱،۲۳۴ تومان', Currency::format(-1234));
    }

    public function test_format_custom_separator(): void
    {
        self::assertSame('1,000 تومان', Currency::format(1000, persianDigits: false, separator: ','));
    }

    public function test_convert_toman_to_rial(): void
    {
        self::assertSame(12340, Currency::convert(1234, Unit::TOMAN, Unit::RIAL));
    }

    public function test_convert_rial_to_toman_integer(): void
    {
        self::assertSame(1234, Currency::convert(12340, Unit::RIAL, Unit::TOMAN));
    }

    public function test_convert_rial_to_toman_non_multiple_yields_float(): void
    {
        self::assertSame(123.5, Currency::convert(1235, Unit::RIAL, Unit::TOMAN));
    }

    public function test_convert_same_unit_identity(): void
    {
        self::assertSame(42, Currency::convert(42, Unit::TOMAN, Unit::TOMAN));
    }

    public function test_format_accepts_amount(): void
    {
        $amount = \Eram\Abzar\Money\Amount::fromToman(1234);
        self::assertSame('۱،۲۳۴ تومان', Currency::format($amount));
        self::assertSame('۱۲،۳۴۰ ریال', Currency::format($amount, Unit::RIAL));
    }

    public function test_format_amount_keeps_sub_toman_rials(): void
    {
        // 12,345 rials is 1,234.5 toman — never silently truncated.
        $amount = \Eram\Abzar\Money\Amount::fromRials(12345);
        self::assertSame('1,234.5 تومان', Currency::format($amount, persianDigits: false, separator: ','));
    }

    public function test_convert_toman_float_to_rial(): void
    {
        self::assertSame(15.0, Currency::convert(1.5, Unit::TOMAN, Unit::RIAL));
    }

    /**
     * @return iterable<string, array{int, int|float}>
     */
    public static function rialToToman(): iterable
    {
        yield 'multiple of 10 stays int' => [30, 3];
        yield 'zero stays int'           => [0, 0];
        yield 'multiple of 9 only'       => [18, 1.8];
        yield 'multiple of 11 only'      => [22, 2.2];
    }

    /**
     * @dataProvider rialToToman
     */
    public function test_convert_rial_to_toman_keeps_type(int $rials, int|float $expected): void
    {
        self::assertSame($expected, Currency::convert($rials, Unit::RIAL, Unit::TOMAN));
    }
}
