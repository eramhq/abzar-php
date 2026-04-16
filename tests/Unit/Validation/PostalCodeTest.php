<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\Validation\ErrorCode;
use Eram\Abzar\Validation\PostalCode;
use PHPUnit\Framework\TestCase;

final class PostalCodeTest extends TestCase
{
    public function test_valid_10_digit_code(): void
    {
        $result = PostalCode::validate('1619735744');
        self::assertTrue($result->isValid());
        $detail = $result->detail();
        self::assertInstanceOf(\Eram\Abzar\Validation\Details\PostalCodeDetails::class, $detail);
        self::assertSame('1619735744', $detail->postalCode);
        self::assertSame('16197', $detail->zoneCode);
    }

    public function test_from_returns_value_object(): void
    {
        $pc = PostalCode::from('16197-35744');
        self::assertSame('1619735744', $pc->value());
        self::assertSame('16197', $pc->zoneCode());
    }

    public function test_from_throws_on_invalid(): void
    {
        $this->expectException(\Eram\Abzar\AbzarValidationException::class);
        PostalCode::from('0000000000');
    }

    public function test_try_from_null_on_invalid(): void
    {
        self::assertNull(PostalCode::tryFrom(''));
    }

    public function test_valid_with_persian_digits(): void
    {
        $result = PostalCode::validate('۱۶۱۹۷۳۵۷۴۴');
        self::assertTrue($result->isValid());
    }

    public function test_valid_with_dash_separator(): void
    {
        $result = PostalCode::validate('16197-35744');
        self::assertTrue($result->isValid());
    }

    public function test_empty_input(): void
    {
        $result = PostalCode::validate('');
        self::assertFalse($result->isValid());
        self::assertSame([ErrorCode::POSTAL_CODE_EMPTY], $result->errorCodes());
    }

    public function test_wrong_length(): void
    {
        $result = PostalCode::validate('12345');
        self::assertFalse($result->isValid());
        self::assertSame([ErrorCode::POSTAL_CODE_WRONG_LENGTH], $result->errorCodes());
    }

    public function test_first_digit_zero_rejected(): void
    {
        $result = PostalCode::validate('0619735744');
        self::assertFalse($result->isValid());
        self::assertSame([ErrorCode::POSTAL_CODE_INVALID_PATTERN], $result->errorCodes());
    }

    public function test_fifth_digit_zero_rejected(): void
    {
        $result = PostalCode::validate('1619035744');
        self::assertFalse($result->isValid());
        self::assertSame([ErrorCode::POSTAL_CODE_INVALID_PATTERN], $result->errorCodes());
    }

    public function test_repeated_run_rejected(): void
    {
        $result = PostalCode::validate('1111235744');
        self::assertFalse($result->isValid());
        self::assertSame([ErrorCode::POSTAL_CODE_INVALID_PATTERN], $result->errorCodes());
    }
}
