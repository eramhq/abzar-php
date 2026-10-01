<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\Exception\ValidationException;
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
        $result = PlateNumber::validate('12ب345-80');
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
        $this->expectException(ValidationException::class);
        PlateNumber::from('not a plate');
    }

    public function test_try_from_null_on_invalid(): void
    {
        $this->assertNull(PlateNumber::tryFrom(''));
    }

    public function test_from_throws_on_unknown_letter(): void
    {
        try {
            PlateNumber::from('12ح345-11');
            $this->fail('expected ValidationException for unknown letter');
        } catch (ValidationException $e) {
            $this->assertSame(ErrorCode::PLATE_NUMBER_UNKNOWN_LETTER, $e->errorCode());
        }
    }

    public function test_try_from_null_on_unknown_letter(): void
    {
        $this->assertNull(PlateNumber::tryFrom('12ح345-11'));
    }

    public function test_from_throws_on_unknown_city_code(): void
    {
        try {
            PlateNumber::from('12ب345-80');
            $this->fail('expected ValidationException for unknown city code');
        } catch (ValidationException $e) {
            $this->assertSame(ErrorCode::PLATE_NUMBER_UNKNOWN_CITY_CODE, $e->errorCode());
        }
    }

    public function test_try_from_null_on_unknown_city_code(): void
    {
        $this->assertNull(PlateNumber::tryFrom('12ب345-80'));
    }

    public function test_stringable_canonical_form(): void
    {
        $plate = PlateNumber::from('12 ب 345 11');
        $this->assertSame('12ب345-11', (string) $plate);
    }

    public function test_fake_returns_valid_plate(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $plate  = PlateNumber::fake();
            $result = PlateNumber::validate($plate);
            $this->assertTrue($result->isValid(), "generated $plate");
            $detail = $result->detail();
            $this->assertInstanceOf(PlateNumberDetails::class, $detail);
            $this->assertNotSame(PlateType::OTHER, $detail->type);
        }
    }

    public function test_fake_honors_pinned_type(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $plate  = PlateNumber::fake(PlateType::TAXI);
            $detail = PlateNumber::validate($plate)->detail();
            $this->assertInstanceOf(PlateNumberDetails::class, $detail);
            $this->assertSame(PlateType::TAXI, $detail->type);
        }
    }

    public function test_fake_rejects_plate_type_other(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PlateNumber::fake(PlateType::OTHER);
    }

    /**
     * B1 — city codes follow the persian-tools numberplate table.
     *
     * @return iterable<string, array{string, string}>
     */
    public static function cityCodeProvinces(): iterable
    {
        yield '77 tehran'      => ['77', 'تهران'];
        yield '10 tehran'      => ['10', 'تهران'];
        yield '13 isfahan'     => ['13', 'اصفهان'];
        yield '14 khuzestan'   => ['14', 'خوزستان'];
        yield '47 markazi'     => ['47', 'مرکزی'];
        yield '49 kohgiluyeh'  => ['49', 'کهگیلویه و بویراحمد'];
        yield '68 alborz'      => ['68', 'البرز'];
        yield '91 ardabil'     => ['91', 'اردبیل'];
        yield '98 ilam'        => ['98', 'ایلام'];
    }

    /**
     * @dataProvider cityCodeProvinces
     */
    public function test_city_code_resolves_to_province(string $code, string $province): void
    {
        $result = PlateNumber::validate('12ب345-' . $code);
        $this->assertTrue($result->isStrictlyValid());
        $detail = $result->detail();
        $this->assertInstanceOf(PlateNumberDetails::class, $detail);
        $this->assertSame($province, $detail->province);
        $this->assertSame([$province], $detail->provinces);
    }

    public function test_shared_city_code_lists_every_province(): void
    {
        // 21 / 30 / 38 / 78 were issued in Tehran province before Alborz split off.
        $detail = PlateNumber::validate('12ب345-21')->detail();
        $this->assertInstanceOf(PlateNumberDetails::class, $detail);
        $this->assertSame(['تهران', 'البرز'], $detail->provinces);
        $this->assertSame('تهران - البرز', $detail->province);
    }

    /**
     * B2 — letter → category follows the persian-tools numberplate table.
     *
     * @return iterable<string, array{string, PlateType}>
     */
    public static function letterTypes(): iterable
    {
        yield 'alef government'   => ['الف', PlateType::GOVERNMENT];
        yield 'be private'        => ['ب', PlateType::PRIVATE];
        yield 'pe police'         => ['پ', PlateType::POLICE];
        yield 'te taxi'           => ['ت', PlateType::TAXI];
        yield 'se military'       => ['ث', PlateType::MILITARY];
        yield 'ze military'       => ['ز', PlateType::MILITARY];
        yield 'zhe disabled'      => ['ژ', PlateType::DISABLED];
        yield 'shin military'     => ['ش', PlateType::MILITARY];
        yield 'ta private'        => ['ط', PlateType::PRIVATE];
        yield 'ein public'        => ['ع', PlateType::PUBLIC];
        yield 'fe military'       => ['ف', PlateType::MILITARY];
        yield 'kaf agricultural'  => ['ک', PlateType::AGRICULTURAL];
        yield 'gaf temporary'     => ['گ', PlateType::TEMPORARY];
        yield 'mim private'       => ['م', PlateType::PRIVATE];
        yield 'D diplomatic'      => ['D', PlateType::DIPLOMATIC];
        yield 'S diplomatic'      => ['S', PlateType::DIPLOMATIC];
    }

    /**
     * @dataProvider letterTypes
     */
    public function test_letter_resolves_to_type(string $letter, PlateType $type): void
    {
        $result = PlateNumber::validate('12' . $letter . '345-11');
        $this->assertTrue($result->isStrictlyValid());
        $detail = $result->detail();
        $this->assertInstanceOf(PlateNumberDetails::class, $detail);
        $this->assertSame($type, $detail->type);
    }

    public function test_arabic_yeh_and_kaf_letters_are_normalized(): void
    {
        // B9 — ي (U+064A) and ك (U+0643) come in from Arabic keyboards.
        $yeh = PlateNumber::validate("12\u{064A}345-11");
        $this->assertTrue($yeh->isStrictlyValid());
        $detail = $yeh->detail();
        $this->assertInstanceOf(PlateNumberDetails::class, $detail);
        $this->assertSame('ی', $detail->letter);
        $this->assertSame(PlateType::PRIVATE, $detail->type);

        $kaf = PlateNumber::validate("12\u{0643}345-11");
        $detail = $kaf->detail();
        $this->assertInstanceOf(PlateNumberDetails::class, $detail);
        $this->assertSame('ک', $detail->letter);
        $this->assertSame(PlateType::AGRICULTURAL, $detail->type);
    }

    public function test_city_code_tolerates_rtl_marks_and_nbsp(): void
    {
        $result = PlateNumber::validate("12\u{00A0}ب\u{200F}345\u{2013}77");
        $this->assertTrue($result->isStrictlyValid());
    }
}
