<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\Exception\ValidationException;
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

    public function test_8_digit_input_rejected_as_likely_truncated(): void
    {
        // '13542419' is the 8-digit round-trip of '0013542419' through an integer
        // column. We reject with guidance rather than silently padding.
        $result = NationalId::validate('13542419');
        $this->assertFalse($result->isValid());
        $this->assertSame([ErrorCode::NATIONAL_ID_LIKELY_TRUNCATED], $result->errorCodes());
    }

    public function test_9_digit_input_rejected_as_likely_truncated(): void
    {
        $result = NationalId::validate('013542419');
        $this->assertFalse($result->isValid());
        $this->assertSame([ErrorCode::NATIONAL_ID_LIKELY_TRUNCATED], $result->errorCodes());
    }

    public function test_padded_short_input_is_valid(): void
    {
        // After the caller pads per the error's guidance, validation succeeds.
        $this->assertTrue(NationalId::validate(str_pad('13542419', 10, '0', STR_PAD_LEFT))->isValid());
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
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
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

    public function test_from_rejects_short_input(): void
    {
        try {
            NationalId::from('13542419');
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertSame(ErrorCode::NATIONAL_ID_LIKELY_TRUNCATED, $e->errorCode());
        }
    }

    public function test_json_serialize(): void
    {
        $ni = NationalId::from('0013542419');
        $payload = $ni->jsonSerialize();
        $this->assertSame('0013542419', $payload['value']);
        $this->assertSame('001', $payload['city_code']);
    }

    public function test_fake_returns_valid_id(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $id = NationalId::fake();
            $this->assertTrue(NationalId::validate($id)->isValid(), "generated $id");
        }
    }

    public function test_fake_honors_city_code(): void
    {
        $id = NationalId::fake('001');
        $this->assertSame('001', substr($id, 0, 3));
        $this->assertTrue(NationalId::validate($id)->isValid());
    }

    public function test_extract_all_pulls_valid_ids_from_text(): void
    {
        $text = 'Customer 0013542419 and their spouse 1234567891 both registered.';
        $hits = NationalId::extractAll($text);
        $this->assertCount(2, $hits);
        $this->assertSame('0013542419', $hits[0]->value());
        $this->assertSame('1234567891', $hits[1]->value());
    }

    public function test_extract_all_skips_invalid_runs(): void
    {
        // Second "0000000000" is 10 digits but rejected as all-same; dropped.
        $text = '0013542419 0000000000';
        $hits = NationalId::extractAll($text);
        $this->assertCount(1, $hits);
    }

    public function test_extract_all_handles_persian_digits(): void
    {
        $hits = NationalId::extractAll('مشتری ۰۰۱۳۵۴۲۴۱۹ ثبت شد');
        $this->assertCount(1, $hits);
        $this->assertSame('0013542419', $hits[0]->value());
    }
}
