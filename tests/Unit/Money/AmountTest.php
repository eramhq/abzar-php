<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Money;

use Eram\Abzar\Exception\FormatException;
use Eram\Abzar\Money\Amount;
use Eram\Abzar\Validation\ErrorCode;
use PHPUnit\Framework\TestCase;

final class AmountTest extends TestCase
{
    public function test_creates_from_rials(): void
    {
        $amount = Amount::fromRials(500_000);

        $this->assertSame(500_000, $amount->inRials());
        $this->assertSame(50_000, $amount->inToman());
    }

    public function test_creates_from_toman(): void
    {
        $amount = Amount::fromToman(50_000);

        $this->assertSame(500_000, $amount->inRials());
        $this->assertSame(50_000, $amount->inToman());
    }

    public function test_from_toman_yields_exact_ten_rials_per_toman(): void
    {
        $this->assertSame(10, Amount::fromToman(1)->inRials());
    }

    public function test_in_toman_truncates_when_rials_not_multiple_of_ten(): void
    {
        $this->assertSame(1, Amount::fromRials(15)->inToman());
    }

    public function test_throws_on_negative_amount(): void
    {
        $this->expectException(FormatException::class);

        try {
            Amount::fromRials(-100);
        } catch (FormatException $e) {
            $this->assertSame(ErrorCode::AMOUNT_NEGATIVE, $e->errorCode());
            throw $e;
        }
    }

    public function test_zero_amount(): void
    {
        $amount = Amount::fromRials(0);

        $this->assertTrue($amount->isZero());
        $this->assertSame(0, $amount->inRials());
        $this->assertSame(0, $amount->inToman());
    }

    public function test_adds_amounts(): void
    {
        $a = Amount::fromToman(10_000);
        $b = Amount::fromToman(20_000);

        $sum = $a->add($b);

        $this->assertSame(30_000, $sum->inToman());
    }

    public function test_subtracts_amounts(): void
    {
        $a = Amount::fromToman(30_000);
        $b = Amount::fromToman(10_000);

        $diff = $a->subtract($b);

        $this->assertSame(20_000, $diff->inToman());
    }

    public function test_subtract_below_zero_throws(): void
    {
        $this->expectException(FormatException::class);

        $a = Amount::fromToman(10_000);
        $b = Amount::fromToman(20_000);

        try {
            $a->subtract($b);
        } catch (FormatException $e) {
            $this->assertSame(ErrorCode::AMOUNT_NEGATIVE, $e->errorCode());
            throw $e;
        }
    }

    public function test_comparison_methods(): void
    {
        $small = Amount::fromToman(10_000);
        $large = Amount::fromToman(20_000);

        $this->assertTrue($large->greaterThan($small));
        $this->assertFalse($small->greaterThan($large));

        $this->assertTrue($small->lessThan($large));
        $this->assertFalse($large->lessThan($small));
    }

    public function test_equality(): void
    {
        $a = Amount::fromRials(100);
        $b = Amount::fromRials(100);
        $c = Amount::fromRials(200);

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function test_immutability(): void
    {
        $original = Amount::fromToman(10_000);
        $added    = $original->add(Amount::fromToman(5_000));

        $this->assertSame(10_000, $original->inToman());
        $this->assertSame(15_000, $added->inToman());
    }

    public function test_json_serializes_to_rials_shape(): void
    {
        $amount = Amount::fromToman(50_000);

        $this->assertSame('{"rials":500000}', json_encode($amount));
    }

    public function test_from_toman_accepts_at_overflow_boundary(): void
    {
        $boundary = intdiv(PHP_INT_MAX, 10);

        $amount = Amount::fromToman($boundary);

        $this->assertSame($boundary * 10, $amount->inRials());
    }

    public function test_from_toman_throws_beyond_overflow_boundary(): void
    {
        $this->expectException(FormatException::class);

        try {
            Amount::fromToman(intdiv(PHP_INT_MAX, 10) + 1);
        } catch (FormatException $e) {
            $this->assertSame(ErrorCode::AMOUNT_OVERFLOW, $e->errorCode());
            throw $e;
        }
    }

    public function test_from_toman_throws_amount_negative_on_php_int_min(): void
    {
        $this->expectException(FormatException::class);

        try {
            Amount::fromToman(PHP_INT_MIN);
        } catch (FormatException $e) {
            $this->assertSame(ErrorCode::AMOUNT_NEGATIVE, $e->errorCode());
            throw $e;
        }
    }

    public function test_from_toman_throws_amount_negative_on_minus_one(): void
    {
        $this->expectException(FormatException::class);

        try {
            Amount::fromToman(-1);
        } catch (FormatException $e) {
            $this->assertSame(ErrorCode::AMOUNT_NEGATIVE, $e->errorCode());
            throw $e;
        }
    }

    public function test_add_succeeds_at_int_max(): void
    {
        $a = Amount::fromRials(PHP_INT_MAX - 100);
        $b = Amount::fromRials(100);

        $sum = $a->add($b);

        $this->assertSame(PHP_INT_MAX, $sum->inRials());
    }

    public function test_add_throws_on_overflow(): void
    {
        $this->expectException(FormatException::class);

        $a = Amount::fromRials(PHP_INT_MAX);
        $b = Amount::fromRials(1);

        try {
            $a->add($b);
        } catch (FormatException $e) {
            $this->assertSame(ErrorCode::AMOUNT_OVERFLOW, $e->errorCode());
            throw $e;
        }
    }

    public function test_greater_than_or_equal(): void
    {
        $small = Amount::fromToman(10_000);
        $same  = Amount::fromToman(10_000);
        $large = Amount::fromToman(20_000);

        $this->assertTrue($small->greaterThanOrEqual($same));
        $this->assertTrue($large->greaterThanOrEqual($small));
        $this->assertFalse($small->greaterThanOrEqual($large));
    }

    public function test_less_than_or_equal(): void
    {
        $small = Amount::fromToman(10_000);
        $same  = Amount::fromToman(10_000);
        $large = Amount::fromToman(20_000);

        $this->assertTrue($small->lessThanOrEqual($same));
        $this->assertTrue($small->lessThanOrEqual($large));
        $this->assertFalse($large->lessThanOrEqual($small));
    }

    public function test_compare_to_returns_tri_state(): void
    {
        $small = Amount::fromToman(10_000);
        $same  = Amount::fromToman(10_000);
        $large = Amount::fromToman(20_000);

        $this->assertSame(-1, $small->compareTo($large));
        $this->assertSame(0, $small->compareTo($same));
        $this->assertSame(1, $large->compareTo($small));
    }

    public function test_compare_to_usort_integration(): void
    {
        $amounts = [
            Amount::fromToman(30_000),
            Amount::fromToman(10_000),
            Amount::fromToman(20_000),
        ];

        usort($amounts, static fn (Amount $a, Amount $b): int => $a->compareTo($b));

        $this->assertSame(
            [10_000, 20_000, 30_000],
            array_map(static fn (Amount $a): int => $a->inToman(), $amounts),
        );
    }

    public function test_times_multiplies_quantity(): void
    {
        $this->assertSame(500, Amount::fromToman(100)->times(5)->inToman());
    }

    public function test_times_by_zero_yields_zero(): void
    {
        $this->assertTrue(Amount::fromToman(100)->times(0)->isZero());
    }

    public function test_times_by_one_is_identity(): void
    {
        $this->assertSame(100, Amount::fromToman(100)->times(1)->inToman());
    }

    public function test_times_on_zero_amount(): void
    {
        $this->assertTrue(Amount::fromRials(0)->times(PHP_INT_MAX)->isZero());
    }

    public function test_times_negative_throws(): void
    {
        $this->expectException(FormatException::class);

        try {
            Amount::fromToman(100)->times(-1);
        } catch (FormatException $e) {
            $this->assertSame(ErrorCode::AMOUNT_NEGATIVE, $e->errorCode());
            throw $e;
        }
    }

    public function test_times_at_overflow_boundary(): void
    {
        $amount = Amount::fromRials(1)->times(PHP_INT_MAX);

        $this->assertSame(PHP_INT_MAX, $amount->inRials());
    }

    public function test_times_beyond_overflow_throws(): void
    {
        $this->expectException(FormatException::class);

        try {
            Amount::fromRials(2)->times(PHP_INT_MAX);
        } catch (FormatException $e) {
            $this->assertSame(ErrorCode::AMOUNT_OVERFLOW, $e->errorCode());
            throw $e;
        }
    }

    public function test_percent_of_applies_int_percentage(): void
    {
        $this->assertSame(90, Amount::fromToman(1_000)->percentOf(9)->inToman());
    }

    public function test_percent_of_applies_float_percentage(): void
    {
        $this->assertSame(50, Amount::fromRials(10_000)->percentOf(0.5)->inRials());
    }

    public function test_percent_of_zero_yields_zero(): void
    {
        $this->assertTrue(Amount::fromToman(1_000)->percentOf(0)->isZero());
    }

    public function test_percent_of_banker_rounding_default(): void
    {
        // 250 × 1 / 100 = 2.5 → banker's rounds to the nearest even integer → 2.
        $this->assertSame(2, Amount::fromRials(250)->percentOf(1)->inRials());
    }

    public function test_percent_of_half_up_override(): void
    {
        // Same input; half-up rounds 2.5 → 3.
        $amount = Amount::fromRials(250)->percentOf(1, PHP_ROUND_HALF_UP);

        $this->assertSame(3, $amount->inRials());
    }

    public function test_percent_of_negative_throws(): void
    {
        $this->expectException(FormatException::class);

        try {
            Amount::fromToman(1_000)->percentOf(-5);
        } catch (FormatException $e) {
            $this->assertSame(ErrorCode::AMOUNT_NEGATIVE, $e->errorCode());
            throw $e;
        }
    }

    public function test_percent_of_nan_throws(): void
    {
        $this->expectException(FormatException::class);

        try {
            Amount::fromToman(1_000)->percentOf(NAN);
        } catch (FormatException $e) {
            $this->assertSame(ErrorCode::AMOUNT_OVERFLOW, $e->errorCode());
            throw $e;
        }
    }

    public function test_percent_of_infinity_throws(): void
    {
        $this->expectException(FormatException::class);

        try {
            Amount::fromToman(1_000)->percentOf(INF);
        } catch (FormatException $e) {
            $this->assertSame(ErrorCode::AMOUNT_OVERFLOW, $e->errorCode());
            throw $e;
        }
    }

    public function test_percent_of_overflow_throws(): void
    {
        $this->expectException(FormatException::class);

        try {
            Amount::fromRials(PHP_INT_MAX)->percentOf(200);
        } catch (FormatException $e) {
            $this->assertSame(ErrorCode::AMOUNT_OVERFLOW, $e->errorCode());
            throw $e;
        }
    }

    public function test_percent_of_throws_at_float_precision_edge(): void
    {
        // PHP_INT_MAX as float becomes 2^63 (not representable exactly); without
        // the `>=` guard, (int) of this float would silently overflow negative.
        $this->expectException(FormatException::class);

        try {
            Amount::fromRials(PHP_INT_MAX)->percentOf(100);
        } catch (FormatException $e) {
            $this->assertSame(ErrorCode::AMOUNT_OVERFLOW, $e->errorCode());
            throw $e;
        }
    }
}
