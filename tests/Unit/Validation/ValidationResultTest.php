<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\Validation\Bank;
use Eram\Abzar\Validation\ErrorCode;
use Eram\Abzar\Validation\Operator;
use Eram\Abzar\Validation\Province;
use Eram\Abzar\Validation\ValidationResult;
use PHPUnit\Framework\TestCase;

class ValidationResultTest extends TestCase
{
    public function test_success_is_valid(): void
    {
        $result = ValidationResult::success();
        $this->assertTrue($result->isValid());
    }

    public function test_success_has_no_errors(): void
    {
        $result = ValidationResult::success();
        $this->assertSame([], $result->errors());
    }

    public function test_success_carries_details(): void
    {
        $result = ValidationResult::success(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $result->details());
    }

    public function test_failure_is_not_valid(): void
    {
        $result = ValidationResult::failure('error');
        $this->assertFalse($result->isValid());
    }

    public function test_failure_with_single_error(): void
    {
        $result = ValidationResult::failure('something went wrong');
        $this->assertSame(['something went wrong'], $result->errors());
    }

    public function test_failure_with_multiple_errors(): void
    {
        $errors = ['error one', 'error two'];
        $result = ValidationResult::failure($errors);
        $this->assertSame($errors, $result->errors());
    }

    public function test_failure_carries_details(): void
    {
        $result = ValidationResult::failure('error', ['context' => 'test']);
        $this->assertSame(['context' => 'test'], $result->details());
    }

    public function test_json_serialize_success(): void
    {
        $result = ValidationResult::success(['bank' => 'بانک ملی ایران']);
        $this->assertSame(
            '{"valid":true,"errors":[],"error_codes":[],"details":{"bank":"بانک ملی ایران"}}',
            json_encode($result, JSON_UNESCAPED_UNICODE),
        );
    }

    public function test_json_serialize_failure(): void
    {
        $result = ValidationResult::failure(['one', 'two'], ['field' => 'input']);
        $this->assertSame(
            [
                'valid'       => false,
                'errors'      => ['one', 'two'],
                'error_codes' => [],
                'details'     => ['field' => 'input'],
            ],
            $result->jsonSerialize(),
        );
    }

    public function test_stringable_valid(): void
    {
        $this->assertSame('valid', (string) ValidationResult::success());
    }

    public function test_stringable_failure_joins_errors(): void
    {
        $result = ValidationResult::failure(['one', 'two']);
        $this->assertSame('one; two', (string) $result);
    }

    public function test_failure_accepts_error_code(): void
    {
        $result = ValidationResult::failure(ErrorCode::CARD_NUMBER_EMPTY);
        $this->assertSame(['شماره کارت نمی‌تواند خالی باشد'], $result->errors());
        $this->assertSame([ErrorCode::CARD_NUMBER_EMPTY], $result->errorCodes());
    }

    public function test_failure_accepts_mixed_codes_and_strings(): void
    {
        $result = ValidationResult::failure([ErrorCode::CARD_NUMBER_EMPTY, 'raw message']);
        $this->assertSame(
            ['شماره کارت نمی‌تواند خالی باشد', 'raw message'],
            $result->errors(),
        );
        $this->assertSame([ErrorCode::CARD_NUMBER_EMPTY], $result->errorCodes());
    }

    public function test_success_with_warnings(): void
    {
        $result = ValidationResult::success([], [ErrorCode::CARD_NUMBER_INVALID_CHECKSUM]);
        $this->assertTrue($result->isValid());
        $this->assertSame(['شماره کارت نامعتبر است'], $result->warnings());
        $this->assertSame([ErrorCode::CARD_NUMBER_INVALID_CHECKSUM], $result->warningCodes());
    }

    public function test_json_includes_error_codes(): void
    {
        $result = ValidationResult::failure(ErrorCode::CARD_NUMBER_EMPTY, ['field' => 'card']);
        $this->assertSame(
            [
                'valid'       => false,
                'errors'      => ['شماره کارت نمی‌تواند خالی باشد'],
                'error_codes' => ['CARD_NUMBER.EMPTY'],
                'details'     => ['field' => 'card'],
            ],
            $result->jsonSerialize(),
        );
    }

    public function test_json_omits_warnings_when_empty(): void
    {
        $payload = ValidationResult::success(['foo' => 1])->jsonSerialize();
        $this->assertArrayNotHasKey('warnings', $payload);
        $this->assertArrayNotHasKey('warning_codes', $payload);
    }

    public function test_json_includes_warnings_when_present(): void
    {
        $result = ValidationResult::success([], [ErrorCode::CARD_NUMBER_INVALID_CHECKSUM]);
        $payload = (array) $result->jsonSerialize();
        $this->assertSame(['شماره کارت نامعتبر است'], $payload['warnings'] ?? null);
        $this->assertSame(['CARD_NUMBER.INVALID_CHECKSUM'], $payload['warning_codes'] ?? null);
    }

    public function test_bank_accessor_resolves_card_surface_form(): void
    {
        $result = ValidationResult::success(['bank' => 'بانک مرکزی ایران']);
        $this->assertSame(Bank::MARKAZI, $result->bank());
    }

    public function test_operator_accessor_resolves_persian_name(): void
    {
        $result = ValidationResult::success(['operator' => 'همراه اول']);
        $this->assertSame(Operator::MCI, $result->operator());
    }

    public function test_province_accessor_resolves_persian_name(): void
    {
        $result = ValidationResult::success(['province' => 'تهران']);
        $this->assertSame(Province::TEHRAN, $result->province());
    }

    public function test_typed_accessors_return_null_when_absent(): void
    {
        $result = ValidationResult::success();
        $this->assertNull($result->bank());
        $this->assertNull($result->operator());
        $this->assertNull($result->province());
    }
}
