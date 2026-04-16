<?php

namespace Eram\Abzar\Tests\Unit\Validation;

use PHPUnit\Framework\TestCase;
use Eram\Abzar\Validation\ValidationResult;

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
            '{"valid":true,"errors":[],"details":{"bank":"بانک ملی ایران"}}',
            json_encode($result, JSON_UNESCAPED_UNICODE),
        );
    }

    public function test_json_serialize_failure(): void
    {
        $result = ValidationResult::failure(['one', 'two'], ['field' => 'input']);
        $this->assertSame(
            ['valid' => false, 'errors' => ['one', 'two'], 'details' => ['field' => 'input']],
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
}
