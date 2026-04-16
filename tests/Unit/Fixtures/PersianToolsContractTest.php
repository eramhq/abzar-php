<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Fixtures;

use Eram\Abzar\Validation\BillId;
use Eram\Abzar\Validation\CardNumber;
use Eram\Abzar\Validation\Iban;
use Eram\Abzar\Validation\NationalId;
use Eram\Abzar\Validation\Operator;
use Eram\Abzar\Validation\PhoneNumber;
use Eram\Abzar\Validation\Province;
use PHPUnit\Framework\TestCase;

/**
 * Contract parity test against the upstream persian-tools JS library.
 *
 * Vectors are lifted from the specs in {@code tests/fixtures/persian-tools/}.
 * Each test skips when the fixture tree hasn't been pulled (`composer fixtures:pull`).
 *
 * The fixtures' MIT LICENSE is vendored alongside the vectors; the {@link test_fixtures_license_is_vendored}
 * assertion blocks accidental removal.
 */
final class PersianToolsContractTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../../fixtures/persian-tools';

    protected function setUp(): void
    {
        if (!is_file(self::FIXTURES_DIR . '/SHA')) {
            self::markTestSkipped('persian-tools fixtures not pulled. Run `composer fixtures:pull`.');
        }
    }

    public function test_fixtures_license_is_vendored(): void
    {
        self::assertFileExists(self::FIXTURES_DIR . '/LICENSE', 'Upstream MIT LICENSE must be vendored alongside fixtures.');
    }

    /**
     * @dataProvider validNationalIds
     */
    public function test_national_id_parity_valid(string $id): void
    {
        self::assertTrue(NationalId::validate($id)->isValid(), "persian-tools accepts $id");
    }

    /**
     * @dataProvider invalidNationalIds
     */
    public function test_national_id_parity_invalid(string $id): void
    {
        self::assertFalse(NationalId::validate($id)->isValid(), "persian-tools rejects $id");
    }

    /** @return iterable<string, array{string}> */
    public static function validNationalIds(): iterable
    {
        // verifyIranianNationalId.spec.ts lines 72-103
        yield '0499370899' => ['0499370899'];
        yield '0790419904' => ['0790419904'];
        yield '1583250689' => ['1583250689'];
        yield '0684159414' => ['0684159414'];
        yield '4400276201' => ['4400276201'];
        yield '2540201288' => ['2540201288'];
    }

    /** @return iterable<string, array{string}> */
    public static function invalidNationalIds(): iterable
    {
        // verifyIranianNationalId.spec.ts lines 20-64
        yield 'empty'             => [''];
        yield 'all zeros'         => ['0000000000'];
        yield 'all nines'         => ['9999999999'];
        yield 'middle zeros'      => ['0010000000'];
        yield 'bad checksum'      => ['1234567890'];
        yield 'all threes'        => ['3333333333'];
    }

    /**
     * @dataProvider validCardNumbers
     */
    public function test_card_number_parity_valid(string $card): void
    {
        self::assertTrue(CardNumber::validate($card)->isValid(), "persian-tools accepts $card");
    }

    /**
     * @dataProvider invalidCardNumbers
     */
    public function test_card_number_parity_invalid(string $card): void
    {
        self::assertFalse(CardNumber::validate($card)->isValid(), "persian-tools rejects $card");
    }

    /** @return iterable<string, array{string}> */
    public static function validCardNumbers(): iterable
    {
        // verifyCardNumber.spec.ts lines 6-9
        yield '6037701689095443' => ['6037701689095443'];
        yield '6219861034529007' => ['6219861034529007'];
    }

    /** @return iterable<string, array{string}> */
    public static function invalidCardNumbers(): iterable
    {
        // verifyCardNumber.spec.ts lines 13-50
        yield 'bad luhn'          => ['6219861034529008'];
        yield 'too short'         => ['621986103452900'];
        yield 'too long'          => ['1234567890123456789'];
        yield 'all zeros'         => ['0000000000000000'];
        yield 'all ones'          => ['1111111111111111'];
        yield 'sequential'        => ['1234567890123456'];
    }

    /**
     * @dataProvider validIbans
     */
    public function test_iban_parity_valid(string $iban): void
    {
        self::assertTrue(Iban::validate($iban)->isValid(), "persian-tools accepts $iban");
    }

    /**
     * @dataProvider invalidIbans
     */
    public function test_iban_parity_invalid(string $iban): void
    {
        self::assertFalse(Iban::validate($iban)->isValid(), "persian-tools rejects $iban");
    }

    /** @return iterable<string, array{string}> */
    public static function validIbans(): iterable
    {
        // sheba.spec.ts lines 7-8, 30-52
        yield 'IR82-parsian'  => ['IR820540102680020817909002'];
        yield 'IR55-pasargad' => ['IR550570022080013447370101'];
        yield 'IR79-shahr'    => ['IR790610000000700796858044'];
    }

    /** @return iterable<string, array{string}> */
    public static function invalidIbans(): iterable
    {
        // sheba.spec.ts lines 12-15
        yield 'too short'        => ['IR01234567890123456789'];
        yield 'too long'         => ['IR012345678901234567890123456789'];
        yield 'bad checksum'     => ['IR012345678901234567890123'];
        yield 'missing prefix'   => ['012345678901234567890123'];
    }

    public function test_iban_bank_lookup_parity(): void
    {
        // sheba.spec.ts lines 30-52
        $r1 = Iban::validate('IR820540102680020817909002');
        self::assertSame('بانک پارسیان', $r1->details()['bank']);

        $r2 = Iban::validate('IR550570022080013447370101');
        self::assertSame('بانک پاسارگاد', $r2->details()['bank']);
    }

    /**
     * @dataProvider validPhoneNumbers
     */
    public function test_phone_number_parity_valid(string $phone): void
    {
        self::assertTrue(PhoneNumber::validate($phone)->isValid(), "persian-tools accepts $phone");
    }

    /**
     * @dataProvider invalidPhoneNumbers
     */
    public function test_phone_number_parity_invalid(string $phone): void
    {
        self::assertFalse(PhoneNumber::validate($phone)->isValid(), "persian-tools rejects $phone");
    }

    /** @return iterable<string, array{string}> */
    public static function validPhoneNumbers(): iterable
    {
        // phoneNumber.spec.ts lines 38-51
        yield '09022002580'      => ['09022002580'];
        yield '09122002580'      => ['09122002580'];
        yield '09322002580'      => ['09322002580'];
        yield '09192002580'      => ['09192002580'];
        yield '09002002580'      => ['09002002580'];
        yield '+989022002580'    => ['+989022002580'];
        yield '989022002580'     => ['989022002580'];
        yield '00989022002580'   => ['00989022002580'];
    }

    /** @return iterable<string, array{string}> */
    public static function invalidPhoneNumbers(): iterable
    {
        // phoneNumber.spec.ts line 54 and line 98
        yield '09802002580' => ['09802002580'];
        yield '99999999999' => ['99999999999'];
    }

    public function test_phone_number_operator_parity(): void
    {
        // phoneNumber.spec.ts lines 7-27 — operator Persian names on the JS side
        // are stored as Persian strings in our DataSources table.
        $cases = [
            '09022002580' => Operator::IRANCELL,
            '09981000000' => Operator::SHATEL_MOBILE,
            '09300880440' => Operator::IRANCELL,
            '09122002580' => Operator::MCI,
        ];

        foreach ($cases as $phone => $expected) {
            $r = PhoneNumber::validate($phone);
            self::assertTrue($r->isValid(), "expected $phone valid");
            self::assertSame($expected, $r->operator(), "operator mismatch for $phone");
        }
    }

    /**
     * @dataProvider provinceLookupVectors
     */
    public function test_province_lookup_parity(string $input, Province $expected): void
    {
        self::assertSame($expected, Province::fromPersian($input));
    }

    /** @return iterable<string, array{string, Province}> */
    public static function provinceLookupVectors(): iterable
    {
        // findCapitalByProvince.spec.ts lines 11-17
        yield 'Tehran'              => ['تهران',          Province::TEHRAN];
        yield 'Markazi'             => ['مرکزی',          Province::MARKAZI];
        yield 'Khorasan Razavi'     => ['خراسان رضوی',    Province::KHORASAN_RAZAVI];
        // Arabic-Yeh tolerance
        yield 'Khorasan Razavi (Arabic yeh)' => ['خراسان رضوي', Province::KHORASAN_RAZAVI];
    }

    /**
     * @dataProvider billValidPairs
     */
    public function test_bill_id_parity_valid(string $billId, string $paymentId): void
    {
        self::assertTrue(BillId::validate($billId, $paymentId)->isValid(), "persian-tools accepts bill $billId/$paymentId");
    }

    /**
     * @dataProvider billInvalidPairs
     */
    public function test_bill_id_parity_invalid(string $billId, string $paymentId): void
    {
        self::assertFalse(BillId::validate($billId, $paymentId)->isValid(), "persian-tools rejects bill $billId/$paymentId");
    }

    /** @return iterable<string, array{string, string}> */
    public static function billValidPairs(): iterable
    {
        // bill.spec.ts lines 76-78, 83-85
        yield 'phone-bill'  => ['7748317800142', '1770160'];
        yield 'water-bill'  => ['2050327604613', '1070189'];
    }

    /** @return iterable<string, array{string, string}> */
    public static function billInvalidPairs(): iterable
    {
        // bill.spec.ts lines 77, 79, 86
        yield 'payment mismatch'   => ['9174639504124', '12908197'];
        yield 'bad bill checksum'  => ['2234322344613', '1070189'];
    }
}
