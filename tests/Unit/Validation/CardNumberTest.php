<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\AbzarValidationException;
use Eram\Abzar\Validation\Bank;
use Eram\Abzar\Validation\CardNumber;
use Eram\Abzar\Validation\Details\CardNumberDetails;
use PHPUnit\Framework\TestCase;

class CardNumberTest extends TestCase
{
    public function test_valid_card(): void
    {
        // 6037991234567893: Luhn sum of first 15 processed = 77, check = 3, total 80 % 10 = 0
        $result = CardNumber::validate('6037991234567893');
        $this->assertTrue($result->isValid());
    }

    public function test_valid_with_spaces(): void
    {
        $result = CardNumber::validate('6037 9912 3456 7893');
        $this->assertTrue($result->isValid());
    }

    public function test_valid_with_dashes(): void
    {
        $result = CardNumber::validate('6037-9912-3456-7893');
        $this->assertTrue($result->isValid());
    }

    public function test_persian_digits(): void
    {
        $result = CardNumber::validate('۶۰۳۷۹۹۱۲۳۴۵۶۷۸۹۳');
        $this->assertTrue($result->isValid());
    }

    public function test_bank_identified(): void
    {
        $result = CardNumber::validate('6037991234567893');
        $this->assertTrue($result->isValid());
        $detail = $result->detail();
        $this->assertInstanceOf(CardNumberDetails::class, $detail);
        $this->assertSame('بانک ملی ایران', $detail->bank);
    }

    public function test_unknown_bin_rejected(): void
    {
        // BIN not in the table — now rejected as invalid rather than accepted with null.
        $result = CardNumber::validate('1234567890123452');
        $this->assertFalse($result->isValid());
    }

    public function test_from_returns_value_object(): void
    {
        $card = CardNumber::from('6037 9912 3456 7893');
        $this->assertSame('6037991234567893', $card->value());
        $this->assertSame('603799', $card->bin());
        $this->assertSame(Bank::MELLI, $card->bankEnum());
    }

    public function test_from_throws_on_invalid(): void
    {
        $this->expectException(AbzarValidationException::class);
        CardNumber::from('6219861234567890');
    }

    public function test_try_from_null_on_invalid(): void
    {
        $this->assertNull(CardNumber::tryFrom('invalid'));
    }

    public function test_invalid_luhn(): void
    {
        $result = CardNumber::validate('6219861234567890');
        $this->assertFalse($result->isValid());
    }

    public function test_too_short(): void
    {
        $result = CardNumber::validate('621986123456');
        $this->assertFalse($result->isValid());
    }

    public function test_too_long(): void
    {
        $result = CardNumber::validate('62198612345678901');
        $this->assertFalse($result->isValid());
    }

    public function test_non_numeric(): void
    {
        $result = CardNumber::validate('6219abcd12345678');
        $this->assertFalse($result->isValid());
    }

    public function test_empty_string(): void
    {
        $result = CardNumber::validate('');
        $this->assertFalse($result->isValid());
    }
}
