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

    public function test_to_string_returns_rials(): void
    {
        $amount = Amount::fromToman(50_000);

        $this->assertSame('500000', (string) $amount);
    }
}
