<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Format;

use Eram\Abzar\Format\Currency;
use Eram\Abzar\Format\CurrencyUnit;
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
            Currency::format(12340, CurrencyUnit::RIAL, persianDigits: false, separator: ','),
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
        self::assertSame(12340, Currency::convert(1234, CurrencyUnit::TOMAN, CurrencyUnit::RIAL));
    }

    public function test_convert_rial_to_toman_integer(): void
    {
        self::assertSame(1234, Currency::convert(12340, CurrencyUnit::RIAL, CurrencyUnit::TOMAN));
    }

    public function test_convert_rial_to_toman_non_multiple_yields_float(): void
    {
        self::assertSame(123.5, Currency::convert(1235, CurrencyUnit::RIAL, CurrencyUnit::TOMAN));
    }

    public function test_convert_same_unit_identity(): void
    {
        self::assertSame(42, Currency::convert(42, CurrencyUnit::TOMAN, CurrencyUnit::TOMAN));
    }
}
