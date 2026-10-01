<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\Exception\ValidationException;
use Eram\Abzar\Validation\Bank;
use Eram\Abzar\Validation\CardNumber;
use Eram\Abzar\Validation\Details\CardNumberDetails;
use Eram\Abzar\Validation\ErrorCode;
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

    public function test_unknown_bin_valid_with_warning(): void
    {
        // Luhn-valid but BIN 123456 is not in the table — accepted with a warning
        // and bank: null, mirroring Iban's unknown-bankCode handling.
        $result = CardNumber::validate('1234567890123452');
        $this->assertTrue($result->isValid());
        $this->assertSame([ErrorCode::CARD_NUMBER_UNKNOWN_BIN], $result->warningCodes());
        $detail = $result->detail();
        $this->assertInstanceOf(CardNumberDetails::class, $detail);
        $this->assertNull($detail->bank);
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
        $this->expectException(ValidationException::class);
        CardNumber::from('6219861234567890');
    }

    public function test_try_from_null_on_invalid(): void
    {
        $this->assertNull(CardNumber::tryFrom('invalid'));
    }

    public function test_from_accepts_unknown_bin_with_null_bank(): void
    {
        // Since 0.7: VOs follow isValid(); warnings no longer block construction.
        $card = CardNumber::from('1234567890123452');
        $this->assertSame('123456', $card->bin());
        $this->assertNull($card->bank());
        $this->assertNull($card->bankEnum());
    }

    public function test_try_from_accepts_unknown_bin(): void
    {
        $this->assertNotNull(CardNumber::tryFrom('1234567890123452'));
    }

    public function test_all_same_digits_has_dedicated_code(): void
    {
        $result = CardNumber::validate('0000000000000000');
        $this->assertFalse($result->isValid());
        $this->assertSame([ErrorCode::CARD_NUMBER_ALL_SAME_DIGITS], $result->errorCodes());
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

    public function test_fake_returns_valid_card(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $card = CardNumber::fake();
            $this->assertTrue(CardNumber::validate($card)->isValid(), "generated $card");
        }
    }

    public function test_fake_honors_bin(): void
    {
        $card = CardNumber::fake('603799');
        $this->assertSame('603799', substr($card, 0, 6));
        $this->assertTrue(CardNumber::validate($card)->isValid());
    }

    public function test_extract_all_pulls_valid_cards_from_text(): void
    {
        $text = 'Paid via 6037991234567893 last week; reserve card 6037 9912 3456 7893 failed.';
        $hits = CardNumber::extractAll($text);
        $this->assertCount(2, $hits);
        $this->assertSame('6037991234567893', $hits[0]->value());
        $this->assertSame('6037991234567893', $hits[1]->value());
    }

    public function test_extract_all_includes_unknown_bin_cards(): void
    {
        $hits = CardNumber::extractAll('Paid 1234567890123452 today');
        $this->assertCount(1, $hits);
        $this->assertNull($hits[0]->bank());
    }

    public function test_extract_all_still_returns_known_bin_cards(): void
    {
        $hits = CardNumber::extractAll('Paid via 6037991234567893 today');
        $this->assertCount(1, $hits);
        $this->assertSame('6037991234567893', $hits[0]->value());
    }

    public function test_extract_all_mixed_input_keeps_order(): void
    {
        $text = 'Known 6037991234567893 and unknown 1234567890123452 side by side.';
        $hits = CardNumber::extractAll($text);
        $this->assertCount(2, $hits);
        $this->assertSame('6037991234567893', $hits[0]->value());
        $this->assertSame('1234567890123452', $hits[1]->value());
    }

    public function test_fake_rejects_malformed_bin_with_validation_exception(): void
    {
        try {
            CardNumber::fake('12');
            $this->fail('expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertSame(ErrorCode::FAKE_INVALID_ARGUMENT, $e->errorCode());
        }
    }

    public function test_formatted_groups_in_four(): void
    {
        $card = CardNumber::from('6037991234567893');
        $this->assertSame('6037 9912 3456 7893', $card->formatted());
    }

    public function test_masked_preserves_first_six_and_last_four(): void
    {
        $card = CardNumber::from('6037991234567893');
        $this->assertSame('6037 99** **** 7893', $card->masked());
    }
}
