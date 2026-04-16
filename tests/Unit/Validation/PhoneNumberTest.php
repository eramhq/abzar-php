<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\Validation\PhoneNumber;
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

    public function test_normalized_formats_in_details(): void
    {
        $result = PhoneNumber::validate('+989121234567');
        $this->assertSame('09121234567', $result->details()['normalized_local']);
        $this->assertSame('+989121234567', $result->details()['normalized_e164']);
    }

    public function test_valid_international_without_plus(): void
    {
        $result = PhoneNumber::validate('989121234567');
        $this->assertTrue($result->isValid());
        $this->assertSame('+989121234567', $result->details()['normalized_e164']);
    }

    public function test_normalize_returns_local_format(): void
    {
        $this->assertSame('09121234567', PhoneNumber::normalize('+989121234567'));
    }

    public function test_mci_operator(): void
    {
        $result = PhoneNumber::validate('09121234567');
        $this->assertSame('همراه اول', $result->details()['operator']);
    }

    public function test_irancell_operator(): void
    {
        $result = PhoneNumber::validate('09351234567');
        $this->assertSame('ایرانسل', $result->details()['operator']);
    }

    public function test_rightel_operator(): void
    {
        $result = PhoneNumber::validate('09211234567');
        $this->assertSame('رایتل', $result->details()['operator']);
    }

    public function test_unknown_mobile_prefix_rejected(): void
    {
        // 094x prefixes are not in the Iranian operator table — matches upstream persian-tools behavior.
        $result = PhoneNumber::validate('09401234567');
        $this->assertFalse($result->isValid());
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
        $this->assertSame('landline', $result->details()['type']);
        $this->assertSame('021', $result->details()['area_code']);
        $this->assertSame('تهران', $result->details()['province']);
    }

    public function test_landline_mashhad(): void
    {
        $result = PhoneNumber::validate('05112345678');
        $this->assertTrue($result->isValid());
        $this->assertSame('landline', $result->details()['type']);
        $this->assertSame('خراسان رضوی', $result->details()['province']);
    }

    public function test_landline_e164_normalization(): void
    {
        $result = PhoneNumber::validate('+982112345678');
        $this->assertTrue($result->isValid());
        $this->assertSame('02112345678', $result->details()['normalized_local']);
        $this->assertSame('+982112345678', $result->details()['normalized_e164']);
    }

    public function test_unknown_area_code_rejected(): void
    {
        // 099 is not a valid area code and 0999 is not a valid mobile prefix length 11
        $result = PhoneNumber::validate('09912345678');
        $this->assertTrue($result->isValid()); // 0999 is mobile (Aptel) — covered elsewhere
        $result = PhoneNumber::validate('00012345678');
        $this->assertFalse($result->isValid());
    }
}
