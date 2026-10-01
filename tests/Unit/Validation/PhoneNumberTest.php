<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\Exception\ValidationException;
use Eram\Abzar\Validation\Details\PhoneNumberDetails;
use Eram\Abzar\Validation\ErrorCode;
use Eram\Abzar\Validation\Operator;
use Eram\Abzar\Validation\PhoneNumber;
use Eram\Abzar\Validation\PhoneNumberType;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    public function test_valid_with_leading_zero(): void
    {
        $result = PhoneNumber::validate('09121234567');
        $this->assertTrue($result->isValid());
    }

    public function test_valid_international_plus98(): void
    {
        $result = PhoneNumber::validate('+989121234567');
        $this->assertTrue($result->isValid());
    }

    public function test_valid_international_0098(): void
    {
        $result = PhoneNumber::validate('00989121234567');
        $this->assertTrue($result->isValid());
    }

    public function test_valid_without_leading_zero(): void
    {
        $result = PhoneNumber::validate('9121234567');
        $this->assertTrue($result->isValid());
    }

    public function test_with_spaces_and_dashes(): void
    {
        $result = PhoneNumber::validate('0912-123-4567');
        $this->assertTrue($result->isValid());
    }

    public function test_persian_digits(): void
    {
        $result = PhoneNumber::validate('۰۹۱۲۱۲۳۴۵۶۷');
        $this->assertTrue($result->isValid());
    }

    public function test_normalized_formats_in_detail(): void
    {
        $result = PhoneNumber::validate('+989121234567');
        $detail = $result->detail();
        $this->assertInstanceOf(PhoneNumberDetails::class, $detail);
        $this->assertSame('09121234567', $detail->normalizedLocal);
        $this->assertSame('+989121234567', $detail->normalizedE164);
    }

    public function test_valid_international_without_plus(): void
    {
        $result = PhoneNumber::validate('989121234567');
        $this->assertTrue($result->isValid());
        $detail = $result->detail();
        $this->assertInstanceOf(PhoneNumberDetails::class, $detail);
        $this->assertSame('+989121234567', $detail->normalizedE164);
    }

    public function test_normalize_returns_local_format(): void
    {
        $this->assertSame('09121234567', PhoneNumber::normalize('+989121234567'));
    }

    public function test_mci_operator(): void
    {
        $result = PhoneNumber::validate('09121234567');
        $detail = $result->detail();
        $this->assertInstanceOf(PhoneNumberDetails::class, $detail);
        $this->assertSame('همراه اول', $detail->operator);
    }

    public function test_irancell_operator(): void
    {
        $result = PhoneNumber::validate('09351234567');
        $detail = $result->detail();
        $this->assertInstanceOf(PhoneNumberDetails::class, $detail);
        $this->assertSame('ایرانسل', $detail->operator);
    }

    public function test_rightel_operator(): void
    {
        $result = PhoneNumber::validate('09211234567');
        $detail = $result->detail();
        $this->assertInstanceOf(PhoneNumberDetails::class, $detail);
        $this->assertSame('رایتل', $detail->operator);
    }

    public function test_unknown_mobile_prefix_valid_with_warning(): void
    {
        // 0940 structure is `09\d{9}` but 940 isn't in DataSources::phoneOperators().
        // Accept as mobile with null operator + PHONE_NUMBER_UNKNOWN_OPERATOR warning.
        $result = PhoneNumber::validate('09401234567');
        $this->assertTrue($result->isValid());
        $this->assertSame([ErrorCode::PHONE_NUMBER_UNKNOWN_OPERATOR], $result->warningCodes());
        $detail = $result->detail();
        $this->assertInstanceOf(PhoneNumberDetails::class, $detail);
        $this->assertNull($detail->operator);
    }

    public function test_landline_without_leading_zero(): void
    {
        // 10-digit landline, leading 0 stripped in round-tripping through integer columns.
        $result = PhoneNumber::validate('2112345678');
        $this->assertTrue($result->isValid());
        $detail = $result->detail();
        $this->assertInstanceOf(PhoneNumberDetails::class, $detail);
        $this->assertSame(PhoneNumberType::LANDLINE, $detail->type);
        $this->assertSame('02112345678', $detail->normalizedLocal);
        $this->assertSame('021', $detail->areaCode);
    }

    public function test_landline_parenthesized_input(): void
    {
        $this->assertTrue(PhoneNumber::validate('(021) 1234-5678')->isValid());
    }

    public function test_landline_e164_parenthesized_input(): void
    {
        $this->assertTrue(PhoneNumber::validate('+98 (21) 1234-5678')->isValid());
    }

    public function test_mobile_dotted_e164_input(): void
    {
        $this->assertTrue(PhoneNumber::validate('+98.9121234567')->isValid());
    }

    public function test_too_short(): void
    {
        $result = PhoneNumber::validate('0912123');
        $this->assertFalse($result->isValid());
    }

    public function test_too_long(): void
    {
        $result = PhoneNumber::validate('091212345678');
        $this->assertFalse($result->isValid());
    }

    public function test_empty_string(): void
    {
        $result = PhoneNumber::validate('');
        $this->assertFalse($result->isValid());
    }

    public function test_landline_tehran(): void
    {
        $result = PhoneNumber::validate('02112345678');
        $this->assertTrue($result->isValid());
        $detail = $result->detail();
        $this->assertInstanceOf(PhoneNumberDetails::class, $detail);
        $this->assertSame(PhoneNumberType::LANDLINE, $detail->type);
        $this->assertSame('021', $detail->areaCode);
        $this->assertSame('تهران', $detail->province);
    }

    public function test_landline_mashhad(): void
    {
        $result = PhoneNumber::validate('05112345678');
        $this->assertTrue($result->isValid());
        $detail = $result->detail();
        $this->assertInstanceOf(PhoneNumberDetails::class, $detail);
        $this->assertSame(PhoneNumberType::LANDLINE, $detail->type);
        $this->assertSame('خراسان رضوی', $detail->province);
    }

    public function test_landline_e164_normalization(): void
    {
        $result = PhoneNumber::validate('+982112345678');
        $this->assertTrue($result->isValid());
        $detail = $result->detail();
        $this->assertInstanceOf(PhoneNumberDetails::class, $detail);
        $this->assertSame('02112345678', $detail->normalizedLocal);
        $this->assertSame('+982112345678', $detail->normalizedE164);
    }

    public function test_0999_classified_as_mobile_aptel(): void
    {
        $this->assertTrue(PhoneNumber::validate('09912345678')->isValid());
    }

    public function test_zero_prefixed_area_code_rejected(): void
    {
        $this->assertFalse(PhoneNumber::validate('00012345678')->isValid());
    }

    public function test_from_returns_value_object(): void
    {
        $phone = PhoneNumber::from('+989121234567');
        $this->assertSame('09121234567', $phone->value());
        $this->assertSame('+989121234567', $phone->e164());
        $this->assertTrue($phone->isMobile());
        $this->assertSame(Operator::MCI, $phone->operatorEnum());
    }

    public function test_from_throws_on_invalid(): void
    {
        $this->expectException(ValidationException::class);
        PhoneNumber::from('invalid');
    }

    public function test_try_from_null_on_invalid(): void
    {
        $this->assertNull(PhoneNumber::tryFrom(''));
    }

    public function test_from_accepts_unknown_operator(): void
    {
        $phone = PhoneNumber::from('09401234567');
        $this->assertTrue($phone->isMobile());
        $this->assertNull($phone->operator());
        $this->assertNull($phone->operatorEnum());
    }

    public function test_try_from_accepts_unknown_operator(): void
    {
        $this->assertNotNull(PhoneNumber::tryFrom('09401234567'));
    }

    public function test_unknown_area_code_valid_with_warning(): void
    {
        // 012 is not an assigned area code; the number still has landline shape.
        $result = PhoneNumber::validate('01212345678');
        $this->assertTrue($result->isValid());
        $this->assertSame([ErrorCode::PHONE_NUMBER_UNKNOWN_AREA_CODE], $result->warningCodes());
        $detail = $result->detail();
        $this->assertInstanceOf(PhoneNumberDetails::class, $detail);
        $this->assertSame(PhoneNumberType::LANDLINE, $detail->type);
        $this->assertSame('012', $detail->areaCode);
        $this->assertNull($detail->city);
        $this->assertNull($detail->province);

        $phone = PhoneNumber::from('012 1234 5678');
        $this->assertSame('+981212345678', $phone->e164());
        $this->assertNull($phone->provinceEnum());
    }

    public function test_landline_shape_requires_area_code_digit_one_to_eight(): void
    {
        $this->assertSame(
            [ErrorCode::PHONE_NUMBER_INVALID_FORMAT],
            PhoneNumber::validate('00123456789')->errorCodes(),
        );
    }

    public function test_from_landline(): void
    {
        $phone = PhoneNumber::from('02112345678');
        $this->assertTrue($phone->isLandline());
        $this->assertSame('021', $phone->areaCode());
        $this->assertSame('تهران', $phone->province());
    }

    public function test_formatted_mobile_local(): void
    {
        $phone = PhoneNumber::from('09121234567');
        $this->assertSame('0912 123 4567', $phone->formatted());
    }

    public function test_formatted_mobile_international(): void
    {
        $phone = PhoneNumber::from('09121234567');
        $this->assertSame('+98 912 123 4567', $phone->formatted(true));
    }

    public function test_formatted_landline_local(): void
    {
        $phone = PhoneNumber::from('02188887777');
        $this->assertSame('021 8888 7777', $phone->formatted());
    }

    public function test_formatted_landline_international_drops_leading_area_zero(): void
    {
        $phone = PhoneNumber::from('02188887777');
        $this->assertSame('+98 21 8888 7777', $phone->formatted(true));
    }

    public function test_fake_returns_valid_mobile(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $number = PhoneNumber::fake();
            $result = PhoneNumber::validate($number);
            $this->assertTrue($result->isValid(), "generated $number");
            $detail = $result->detail();
            $this->assertInstanceOf(PhoneNumberDetails::class, $detail);
            $this->assertSame(PhoneNumberType::MOBILE, $detail->type);
        }
    }

    public function test_fake_honors_operator_prefix(): void
    {
        $number = PhoneNumber::fake('912');
        $this->assertSame('0912', substr($number, 0, 4));
        $this->assertTrue(PhoneNumber::validate($number)->isValid());
    }

    public function test_fake_rejects_non_three_digit_prefix(): void
    {
        $this->expectException(ValidationException::class);
        PhoneNumber::fake('12');
    }

    public function test_fake_landline(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $number = PhoneNumber::fake(type: PhoneNumberType::LANDLINE);
            $result = PhoneNumber::validate($number);
            $this->assertTrue($result->isStrictlyValid(), "generated $number");
            $detail = $result->detail();
            $this->assertInstanceOf(PhoneNumberDetails::class, $detail);
            $this->assertSame(PhoneNumberType::LANDLINE, $detail->type);
        }
    }

    public function test_fake_landline_honors_area_code(): void
    {
        $number = PhoneNumber::fake(type: PhoneNumberType::LANDLINE, areaCode: '021');
        $this->assertStringStartsWith('021', $number);
        $this->assertSame('تهران', PhoneNumber::from($number)->city());
    }

    public function test_fake_rejects_operator_prefix_for_landline(): void
    {
        $this->expectException(ValidationException::class);
        PhoneNumber::fake('912', PhoneNumberType::LANDLINE);
    }

    public function test_masked(): void
    {
        $this->assertSame('0912 *** 4567', PhoneNumber::from('09121234567')->masked());
        $this->assertSame('021 **** 7777', PhoneNumber::from('02188887777')->masked());
    }

    public function test_extract_all(): void
    {
        $text = 'Call ۰۹۱۲ ۱۲۳ ۴۵۶۷ or +98 21 8888 7777, not 12345 or 091212345678.';
        $hits = PhoneNumber::extractAll($text);
        $this->assertSame(['09121234567', '02188887777'], array_map(static fn (PhoneNumber $p): string => $p->value(), $hits));
    }

    public function test_karaj_area_code_resolves_to_alborz(): void
    {
        $phone = PhoneNumber::from('02632123456');
        $this->assertSame('کرج', $phone->city());
        $this->assertSame('البرز', $phone->province());
        $this->assertSame(\Eram\Abzar\Validation\Province::ALBORZ, $phone->provinceEnum());
    }
}
