<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Format;

use Eram\Abzar\AbzarException;
use Eram\Abzar\AbzarFormatException;
use Eram\Abzar\Format\NumberToWords;
use Eram\Abzar\Validation\ErrorCode;
use PHPUnit\Framework\TestCase;

class NumberToWordsTest extends TestCase
{
    public function test_zero(): void
    {
        $this->assertSame('صفر', NumberToWords::convert(0));
    }

    public function test_single_digits(): void
    {
        $this->assertSame('یک', NumberToWords::convert(1));
        $this->assertSame('پنج', NumberToWords::convert(5));
        $this->assertSame('نه', NumberToWords::convert(9));
    }

    public function test_teens(): void
    {
        $this->assertSame('ده', NumberToWords::convert(10));
        $this->assertSame('یازده', NumberToWords::convert(11));
        $this->assertSame('پانزده', NumberToWords::convert(15));
        $this->assertSame('نوزده', NumberToWords::convert(19));
    }

    public function test_tens(): void
    {
        $this->assertSame('بیست', NumberToWords::convert(20));
        $this->assertSame('سی', NumberToWords::convert(30));
        $this->assertSame('نود', NumberToWords::convert(90));
    }

    public function test_hundreds(): void
    {
        $this->assertSame('یکصد', NumberToWords::convert(100));
        $this->assertSame('دویست', NumberToWords::convert(200));
        $this->assertSame('نهصد', NumberToWords::convert(900));
    }

    public function test_composite(): void
    {
        $this->assertSame('یکصد و بیست و سه', NumberToWords::convert(123));
    }

    public function test_thousand(): void
    {
        $this->assertSame('یک هزار', NumberToWords::convert(1000));
    }

    public function test_million(): void
    {
        $this->assertSame('یک میلیون', NumberToWords::convert(1000000));
    }

    public function test_complex_large(): void
    {
        $this->assertSame(
            'یک میلیون و دویست و سی و چهار هزار و پانصد و شصت و هفت',
            NumberToWords::convert(1234567)
        );
    }

    public function test_billion(): void
    {
        $this->assertSame('یک میلیارد', NumberToWords::convert(1000000000));
    }

    public function test_negative(): void
    {
        $this->assertSame('منفی پنج', NumberToWords::convert(-5));
    }

    public function test_decimal(): void
    {
        $this->assertSame('یک ممیز پنج', NumberToWords::convert(1.5));
    }

    public function test_decimal_multi_digits(): void
    {
        $this->assertSame('سه ممیز بیست و پنج', NumberToWords::convert(3.25));
    }

    public function test_integer_as_float(): void
    {
        $this->assertSame('یک', NumberToWords::convert(1.0));
    }

    public function test_decimal_with_leading_zero(): void
    {
        $this->assertSame('سه ممیز صفر پنج', NumberToWords::convert(3.05));
    }

    public function test_decimal_with_multiple_leading_zeros(): void
    {
        $this->assertSame('سه ممیز صفر صفر پنج', NumberToWords::convert(3.005));
    }

    public function test_decimal_with_zero_prefix_and_composite(): void
    {
        $this->assertSame('سه ممیز صفر بیست و پنج', NumberToWords::convert(3.025));
    }

    public function test_decimal_zero_integer_with_leading_zero(): void
    {
        $this->assertSame('صفر ممیز صفر پنج', NumberToWords::convert(0.05));
    }

    public function test_quintillion(): void
    {
        $this->assertSame('یک کوینتیلیون', NumberToWords::convert(1_000_000_000_000_000_000));
    }

    public function test_throws_abzar_format_exception_for_float_above_int_max(): void
    {
        try {
            NumberToWords::convert(1e20);
            $this->fail('Expected AbzarFormatException was not thrown.');
        } catch (AbzarFormatException $e) {
            $this->assertSame(ErrorCode::NUMBER_TO_WORDS_OUT_OF_RANGE, $e->errorCode());
        }
    }

    public function test_overflow_is_catchable_via_abzar_exception(): void
    {
        $this->expectException(AbzarException::class);
        NumberToWords::convert(1e20);
    }

    public function test_negative_float_above_int_max_also_throws(): void
    {
        try {
            NumberToWords::convert(-1e20);
            $this->fail('Expected AbzarFormatException was not thrown.');
        } catch (AbzarFormatException $e) {
            $this->assertSame(ErrorCode::NUMBER_TO_WORDS_OUT_OF_RANGE, $e->errorCode());
        }
    }

    public function test_float_beyond_precision_throws(): void
    {
        try {
            // More than PHP_FLOAT_DIG significant digits — IEEE-754 rounding
            // means the converted word no longer matches what the caller typed.
            NumberToWords::convert(1.234567890123456789);
            $this->fail('Expected AbzarFormatException was not thrown.');
        } catch (AbzarFormatException $e) {
            $this->assertSame(ErrorCode::NUMBER_TO_WORDS_PRECISION_LOSS, $e->errorCode());
        }
    }
}
