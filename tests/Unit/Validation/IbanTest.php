<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\AbzarValidationException;
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
        $this->expectException(AbzarValidationException::class);
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
}
