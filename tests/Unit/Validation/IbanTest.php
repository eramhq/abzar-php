<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\Exception\ValidationException;
use Eram\Abzar\Validation\Details\IbanDetails;
use Eram\Abzar\Validation\ErrorCode;
use Eram\Abzar\Validation\Iban;
use PHPUnit\Framework\TestCase;

class IbanTest extends TestCase
{
    public function test_valid_iban(): void
    {
        // IR062960000000100324200001 - Bank Melli
        // Let me use a known valid Iranian IBAN
        // IR820540102680020817909002 - Parsian Bank
        $result = Iban::validate('IR820540102680020817909002');
        $this->assertTrue($result->isValid());
    }

    public function test_valid_lowercase(): void
    {
        $result = Iban::validate('ir820540102680020817909002');
        $this->assertTrue($result->isValid());
    }

    public function test_valid_with_spaces(): void
    {
        $result = Iban::validate('IR82 0540 1026 8002 0817 9090 02');
        $this->assertTrue($result->isValid());
    }

    public function test_valid_without_prefix(): void
    {
        $result = Iban::validate('820540102680020817909002');
        $this->assertTrue($result->isValid());
    }

    public function test_persian_digits(): void
    {
        $result = Iban::validate('IR۸۲۰۵۴۰۱۰۲۶۸۰۰۲۰۸۱۷۹۰۹۰۰۲');
        $this->assertTrue($result->isValid());
    }

    public function test_bank_identified(): void
    {
        $result = Iban::validate('IR820540102680020817909002');
        $this->assertTrue($result->isValid());
        $detail = $result->detail();
        $this->assertInstanceOf(IbanDetails::class, $detail);
        $this->assertSame('بانک پارسیان', $detail->bank);
        $this->assertSame('054', $detail->bankCode);
    }

    public function test_invalid_mod97(): void
    {
        // Tamper a digit
        $result = Iban::validate('IR820540102680020817909003');
        $this->assertFalse($result->isValid());
    }

    public function test_too_short(): void
    {
        $result = Iban::validate('IR82054010268');
        $this->assertFalse($result->isValid());
    }

    public function test_too_long(): void
    {
        $result = Iban::validate('IR8205401026800208179090020000');
        $this->assertFalse($result->isValid());
    }

    public function test_non_ir_prefix(): void
    {
        $result = Iban::validate('DE820540102680020817909002');
        $this->assertFalse($result->isValid());
    }

    public function test_empty_string(): void
    {
        $result = Iban::validate('');
        $this->assertFalse($result->isValid());
    }

    public function test_unknown_bank(): void
    {
        $result = Iban::validate('IR820540102680020817909002');
        $detail = $result->detail();
        $this->assertInstanceOf(IbanDetails::class, $detail);
        $this->assertNotNull($detail->bank);
    }

    public function test_from_returns_value_object(): void
    {
        $iban = Iban::from('IR820540102680020817909002');
        $this->assertSame('IR820540102680020817909002', $iban->value());
        $this->assertSame('054', $iban->bankCode());
        $this->assertSame('بانک پارسیان', $iban->bank());
    }

    public function test_from_throws_on_invalid(): void
    {
        $this->expectException(ValidationException::class);
        Iban::from('IR820540102680020817909003');
    }

    public function test_try_from_null_on_invalid(): void
    {
        $this->assertNull(Iban::tryFrom('invalid'));
    }

    public function test_from_normalizes_input(): void
    {
        $iban = Iban::from('ir82 0540 1026 8002 0817 9090 02');
        $this->assertSame('IR820540102680020817909002', $iban->value());
    }

    public function test_validation_error_code_missing_prefix(): void
    {
        $result = Iban::validate('DE820540102680020817909002');
        $this->assertSame([ErrorCode::IBAN_MISSING_PREFIX], $result->errorCodes());
    }

    public function test_formatted_four_char_groups_with_two_tail(): void
    {
        $iban = Iban::from('IR820540102680020817909002');
        $this->assertSame('IR82 0540 1026 8002 0817 9090 02', $iban->formatted());
    }

    public function test_fake_returns_valid_iban(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $iban = Iban::fake();
            $this->assertTrue(Iban::validate($iban)->isValid(), "generated $iban");
            $this->assertSame('IR', substr($iban, 0, 2));
            $this->assertSame(26, strlen($iban));
        }
    }

    public function test_fake_honors_bank_code(): void
    {
        $iban = Iban::fake('054');
        $this->assertSame('054', substr($iban, 4, 3));
        $this->assertTrue(Iban::validate($iban)->isValid());
    }

    public function test_fake_rejects_non_three_digit_bank_code(): void
    {
        $this->expectException(ValidationException::class);
        Iban::fake('12');
    }

    public function test_unknown_bank_code_valid_with_warning(): void
    {
        $result = Iban::validate('IR940990278828365058148453');
        $this->assertTrue($result->isValid());
        $this->assertSame([ErrorCode::IBAN_UNKNOWN_BANK], $result->warningCodes());
        $iban = Iban::from('IR940990278828365058148453');
        $this->assertSame('099', $iban->bankCode());
        $this->assertNull($iban->bank());
    }

    public function test_known_bank_code_has_no_warning(): void
    {
        $this->assertTrue(Iban::validate('IR820540102680020817909002')->isStrictlyValid());
    }

    public function test_masked(): void
    {
        $this->assertSame(
            'IR82 054* **** **** **** **90 02',
            Iban::from('IR820540102680020817909002')->masked(),
        );
    }

    public function test_extract_all(): void
    {
        $text = 'شبا: IR82 0540 1026 8002 0817 9090 02 و ir820540102680020817909002؛ نامعتبر IR820540102680020817909003';
        $hits = Iban::extractAll($text);
        $this->assertCount(2, $hits);
        $this->assertSame('IR820540102680020817909002', $hits[0]->value());
        $this->assertSame('IR820540102680020817909002', $hits[1]->value());
    }

    public function test_accepts_iban_with_dashes_and_invisible_marks(): void
    {
        // B4 — Iban previously skipped the shared input cleaner.
        $this->assertTrue(Iban::validate('IR82-0540-1026-8002-0817-9090-02')->isValid());
        $this->assertTrue(Iban::validate("\u{200E}IR82\u{00A0}0540\u{00A0}1026\u{00A0}8002\u{00A0}0817\u{00A0}9090\u{00A0}02")->isValid());
        $this->assertSame('IR820540102680020817909002', Iban::from("\u{200F}ir82 0540 1026 8002 0817 9090 02")->value());
    }
}
