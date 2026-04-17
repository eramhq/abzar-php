<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\AbzarValidationException;
use Eram\Abzar\Validation\Details\PlateNumberDetails;
use Eram\Abzar\Validation\ErrorCode;
use Eram\Abzar\Validation\PlateNumber;
use Eram\Abzar\Validation\PlateType;
use PHPUnit\Framework\TestCase;

final class PlateNumberTest extends TestCase
{
    public function test_parses_canonical_tehran_private(): void
    {
        $result = PlateNumber::validate('12ب345-67');
        $this->assertTrue($result->isValid());
        $detail = $result->detail();
        $this->assertInstanceOf(PlateNumberDetails::class, $detail);
        $this->assertSame('12', $detail->twoDigit);
        $this->assertSame('ب', $detail->letter);
        $this->assertSame('345', $detail->threeDigit);
        $this->assertSame('67', $detail->cityCode);
        $this->assertSame(PlateType::PRIVATE, $detail->type);
    }

    public function test_parses_whitespace_separated(): void
    {
        $result = PlateNumber::validate('12 ب 345 11');
        $this->assertTrue($result->isValid());
        $detail = $result->detail();
        $this->assertInstanceOf(PlateNumberDetails::class, $detail);
        $this->assertSame('تهران', $detail->province);
    }

    public function test_parses_persian_digits(): void
    {
        $result = PlateNumber::validate('۱۲ب۳۴۵-۱۱');
        $this->assertTrue($result->isValid());
    }

    public function test_taxi_letter_type(): void
    {
        $detail = PlateNumber::validate('12ت345-11')->detail();
        $this->assertInstanceOf(PlateNumberDetails::class, $detail);
        $this->assertSame(PlateType::TAXI, $detail->type);
    }

    public function test_empty_input_rejected(): void
    {
        $result = PlateNumber::validate('');
        $this->assertSame([ErrorCode::PLATE_NUMBER_EMPTY], $result->errorCodes());
    }

    public function test_missing_letter_rejected(): void
    {
        $result = PlateNumber::validate('12345-11');
        $this->assertSame([ErrorCode::PLATE_NUMBER_INVALID_FORMAT], $result->errorCodes());
    }

    public function test_unknown_letter_resolves_to_other_with_warning(): void
    {
        // ح isn't in the plate letter table; it's a valid Arabic letter though,
        // so we accept the plate and tag the type as OTHER with a warning.
        $result = PlateNumber::validate('12ح345-11');
        $this->assertTrue($result->isValid());
        $this->assertContains(ErrorCode::PLATE_NUMBER_UNKNOWN_LETTER, $result->warningCodes());
        $detail = $result->detail();
        $this->assertInstanceOf(PlateNumberDetails::class, $detail);
        $this->assertSame(PlateType::OTHER, $detail->type);
    }

    public function test_unknown_city_code_valid_with_warning(): void
    {
        $result = PlateNumber::validate('12ب345-99');
        $this->assertTrue($result->isValid());
        $this->assertSame([ErrorCode::PLATE_NUMBER_UNKNOWN_CITY_CODE], $result->warningCodes());
    }

    public function test_from_returns_value_object(): void
    {
        $plate = PlateNumber::from('12ب345-11');
        $this->assertSame('ب', $plate->letter());
        $this->assertSame(PlateType::PRIVATE, $plate->type());
        $this->assertSame('تهران', $plate->province());
    }

    public function test_from_throws_on_invalid(): void
    {
        $this->expectException(AbzarValidationException::class);
        PlateNumber::from('not a plate');
    }

    public function test_try_from_null_on_invalid(): void
    {
        $this->assertNull(PlateNumber::tryFrom(''));
    }

    public function test_stringable_canonical_form(): void
    {
        $plate = PlateNumber::from('12 ب 345 11');
        $this->assertSame('12ب345-11', (string) $plate);
    }
}
