<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\Validation\Details\NationalIdDetails;
use Eram\Abzar\Validation\ErrorCode;
use Eram\Abzar\Validation\ValidationResult;
use PHPUnit\Framework\TestCase;

class ValidationResultTest extends TestCase
{
    public function test_valid_is_valid(): void
    {
        $result = ValidationResult::valid();
        $this->assertTrue($result->isValid());
    }

    public function test_valid_has_no_errors(): void
    {
        $result = ValidationResult::valid();
        $this->assertSame([], $result->errors());
    }

    public function test_valid_carries_detail(): void
    {
        $detail = new NationalIdDetails('0013542419', '001', 'تهران', 'تهران');
        $result = ValidationResult::valid($detail);
        $this->assertSame($detail, $result->detail());
    }

    public function test_invalid_is_not_valid(): void
    {
        $result = ValidationResult::invalid('error');
        $this->assertFalse($result->isValid());
    }

    public function test_invalid_with_single_error(): void
    {
        $result = ValidationResult::invalid('something went wrong');
        $this->assertSame(['something went wrong'], $result->errors());
    }

    public function test_invalid_with_multiple_errors(): void
    {
        $errors = ['error one', 'error two'];
        $result = ValidationResult::invalid($errors);
        $this->assertSame($errors, $result->errors());
    }

    public function test_invalid_has_null_detail(): void
    {
        $result = ValidationResult::invalid('error');
        $this->assertNull($result->detail());
    }

    public function test_json_serialize_valid(): void
    {
        $detail = new NationalIdDetails('0013542419', '001', 'تهران', 'تهران');
        $result = ValidationResult::valid($detail);
        $this->assertSame(
            '{"valid":true,"errors":[],"error_codes":[],"detail":{"value":"0013542419","city_code":"001","city":"تهران","province":"تهران"}}',
            json_encode($result, JSON_UNESCAPED_UNICODE),
        );
    }

    public function test_json_serialize_invalid(): void
    {
        $result = ValidationResult::invalid(['one', 'two']);
        $this->assertSame(
            [
                'valid'       => false,
                'errors'      => ['one', 'two'],
                'error_codes' => [],
                'detail'      => null,
            ],
            $result->jsonSerialize(),
        );
    }

    public function test_stringable_valid(): void
    {
        $this->assertSame('valid', (string) ValidationResult::valid());
    }

    public function test_stringable_invalid_joins_errors(): void
    {
        $result = ValidationResult::invalid(['one', 'two']);
        $this->assertSame('one; two', (string) $result);
    }

    public function test_invalid_accepts_error_code(): void
    {
        $result = ValidationResult::invalid(ErrorCode::CARD_NUMBER_EMPTY);
        $this->assertSame(['شماره کارت نمی‌تواند خالی باشد'], $result->errors());
        $this->assertSame([ErrorCode::CARD_NUMBER_EMPTY], $result->errorCodes());
    }

    public function test_invalid_accepts_mixed_codes_and_strings(): void
    {
        $result = ValidationResult::invalid([ErrorCode::CARD_NUMBER_EMPTY, 'raw message']);
        $this->assertSame(
            ['شماره کارت نمی‌تواند خالی باشد', 'raw message'],
            $result->errors(),
        );
        $this->assertSame([ErrorCode::CARD_NUMBER_EMPTY], $result->errorCodes());
    }

    public function test_valid_with_warnings(): void
    {
        $result = ValidationResult::validWithWarnings(ErrorCode::CARD_NUMBER_INVALID_CHECKSUM);
        $this->assertTrue($result->isValid());
        $this->assertSame(['شماره کارت نامعتبر است'], $result->warnings());
        $this->assertSame([ErrorCode::CARD_NUMBER_INVALID_CHECKSUM], $result->warningCodes());
    }

    public function test_valid_with_warnings_and_detail(): void
    {
        $detail = new NationalIdDetails('0013542419', '001', 'تهران', 'تهران');
        $result = ValidationResult::validWithWarnings(
            [ErrorCode::CARD_NUMBER_INVALID_CHECKSUM],
            $detail,
        );
        $this->assertTrue($result->isValid());
        $this->assertSame($detail, $result->detail());
        $this->assertSame([ErrorCode::CARD_NUMBER_INVALID_CHECKSUM], $result->warningCodes());
    }

    public function test_json_includes_error_codes(): void
    {
        $result = ValidationResult::invalid(ErrorCode::CARD_NUMBER_EMPTY);
        $this->assertSame(
            [
                'valid'       => false,
                'errors'      => ['شماره کارت نمی‌تواند خالی باشد'],
                'error_codes' => ['CARD_NUMBER.EMPTY'],
                'detail'      => null,
            ],
            $result->jsonSerialize(),
        );
    }

    public function test_json_omits_warnings_when_empty(): void
    {
        $payload = ValidationResult::valid()->jsonSerialize();
        $this->assertArrayNotHasKey('warnings', $payload);
        $this->assertArrayNotHasKey('warning_codes', $payload);
    }

    public function test_json_includes_warnings_when_present(): void
    {
        $result = ValidationResult::validWithWarnings([ErrorCode::CARD_NUMBER_INVALID_CHECKSUM]);
        $payload = (array) $result->jsonSerialize();
        $this->assertSame(['شماره کارت نامعتبر است'], $payload['warnings'] ?? null);
        $this->assertSame(['CARD_NUMBER.INVALID_CHECKSUM'], $payload['warning_codes'] ?? null);
    }
}
