<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\AbzarValidationException;
use Eram\Abzar\Validation\Details\NationalIdDetails;
use Eram\Abzar\Validation\ErrorCode;
use Eram\Abzar\Validation\NationalId;
use PHPUnit\Framework\TestCase;

class NationalIdTest extends TestCase
{
    public function test_valid_national_id(): void
    {
        $result = NationalId::validate('1234567891');
        $this->assertTrue($result->isValid());
    }

    public function test_valid_id_with_leading_zeros(): void
    {
        $result = NationalId::validate('0013542419');
        $this->assertTrue($result->isValid());
    }

    public function test_valid_id_returns_city_and_province(): void
    {
        $result = NationalId::validate('1234567891');
        $this->assertTrue($result->isValid());
        $detail = $result->detail();
        $this->assertInstanceOf(NationalIdDetails::class, $detail);
        $this->assertNotSame('', $detail->cityCode);
    }

    public function test_persian_digit_input(): void
    {
        $result = NationalId::validate('۱۲۳۴۵۶۷۸۹۱');
        $this->assertTrue($result->isValid());
    }

    public function test_arabic_digit_input(): void
    {
        $result = NationalId::validate('١٢٣٤٥٦٧٨٩١');
        $this->assertTrue($result->isValid());
    }

    public function test_all_same_digits_rejected(): void
    {
        for ($d = 0; $d <= 9; $d++) {
            $result = NationalId::validate(str_repeat((string) $d, 10));
            $this->assertFalse($result->isValid(), "All {$d}s should be rejected");
        }
    }

    public function test_too_short(): void
    {
        $result = NationalId::validate('12345');
        $this->assertFalse($result->isValid());
    }

    public function test_too_long(): void
    {
        $result = NationalId::validate('123456789012');
        $this->assertFalse($result->isValid());
    }

    public function test_non_numeric(): void
    {
        $result = NationalId::validate('12345abcde');
        $this->assertFalse($result->isValid());
    }

    public function test_empty_string(): void
    {
        $result = NationalId::validate('');
        $this->assertFalse($result->isValid());
    }

    public function test_invalid_checksum(): void
    {
        $result = NationalId::validate('1234567890');
        $this->assertFalse($result->isValid());
    }

    public function test_8_digit_input_padded(): void
    {
        $result = NationalId::validate('13542419');
        $this->assertTrue($result->isValid());
    }

    public function test_unknown_city_code(): void
    {
        $result = NationalId::validate('7751000007');
        $this->assertTrue($result->isValid());
        $detail = $result->detail();
        $this->assertInstanceOf(NationalIdDetails::class, $detail);
        $this->assertNull($detail->city);
        $this->assertNull($detail->province);
    }

    public function test_from_returns_value_object(): void
    {
        $ni = NationalId::from('0013542419');
        $this->assertSame('0013542419', $ni->value());
        $this->assertSame('0013542419', (string) $ni);
        $this->assertSame('001', $ni->cityCode());
    }

    public function test_from_throws_on_invalid(): void
    {
        try {
            NationalId::from('0000000000');
            $this->fail('Expected AbzarValidationException');
        } catch (AbzarValidationException $e) {
            $this->assertSame(ErrorCode::NATIONAL_ID_ALL_SAME_DIGITS, $e->errorCode());
            $this->assertFalse($e->result()->isValid());
        }
    }

    public function test_try_from_returns_null_on_invalid(): void
    {
        $this->assertNull(NationalId::tryFrom('0000000000'));
    }

    public function test_try_from_returns_instance_on_valid(): void
    {
        $ni = NationalId::tryFrom('۱۲۳۴۵۶۷۸۹۱');
        $this->assertNotNull($ni);
        $this->assertSame('1234567891', $ni->value());
    }

    public function test_from_pads_8_or_9_digit_input(): void
    {
        $ni = NationalId::from('13542419');
        $this->assertSame('0013542419', $ni->value());
    }

    public function test_json_serialize(): void
    {
        $ni = NationalId::from('0013542419');
        $payload = $ni->jsonSerialize();
        $this->assertSame('0013542419', $payload['value']);
        $this->assertSame('001', $payload['city_code']);
    }
}
