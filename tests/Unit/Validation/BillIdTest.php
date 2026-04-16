<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\Validation\BillId;
use Eram\Abzar\Validation\BillType;
use Eram\Abzar\Validation\Details\BillIdDetails;
use Eram\Abzar\Validation\ErrorCode;
use PHPUnit\Framework\TestCase;

final class BillIdTest extends TestCase
{
    public function test_empty_inputs_fail(): void
    {
        $result = BillId::validate('', '');
        self::assertFalse($result->isValid());
        self::assertSame([ErrorCode::BILL_ID_EMPTY], $result->errorCodes());
    }

    public function test_short_inputs_fail(): void
    {
        $result = BillId::validate('12', '12');
        self::assertFalse($result->isValid());
        self::assertSame([ErrorCode::BILL_ID_WRONG_LENGTH], $result->errorCodes());
    }

    public function test_bill_id_checksum_mismatch_fails(): void
    {
        // Deliberately invalid last digit.
        $result = BillId::validate('1234567899', '123456');
        self::assertFalse($result->isValid());
        self::assertSame([ErrorCode::BILL_ID_INVALID_CHECKSUM], $result->errorCodes());
    }

    /**
     * Test vectors sourced from persian-tools/test/bill.spec.ts.
     *
     * @dataProvider upstreamValidPairs
     */
    public function test_upstream_valid_pairs_pass(string $billId, string $paymentId, BillType $type): void
    {
        $result = BillId::validate($billId, $paymentId);
        self::assertTrue($result->isValid(), 'errors: ' . implode('; ', $result->errors()));
        $detail = $result->detail();
        self::assertInstanceOf(BillIdDetails::class, $detail);
        self::assertSame($type, $detail->type);
    }

    /**
     * @return iterable<string, array{string, string, BillType}>
     */
    public static function upstreamValidPairs(): iterable
    {
        yield 'phone' => ['7748317800142', '1770160', BillType::PHONE];
        yield 'water' => ['2050327604613', '1070189', BillType::WATER];
    }

    /**
     * @dataProvider upstreamInvalidPairs
     */
    public function test_upstream_invalid_pairs_fail(string $billId, string $paymentId): void
    {
        $result = BillId::validate($billId, $paymentId);
        self::assertFalse($result->isValid());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function upstreamInvalidPairs(): iterable
    {
        // persian-tools marks these as isValid=false.
        yield 'bad bill checksum'       => ['2234322344613', '1070189'];
        yield 'payment mismatch'        => ['9174639504124', '12908197'];
    }

    public function test_payment_id_mismatch_fails(): void
    {
        // Upstream valid bill, wrong payment-check digits.
        $result = BillId::validate('7748317800142', '1770199');
        self::assertFalse($result->isValid());
        self::assertSame([ErrorCode::BILL_ID_PAYMENT_MISMATCH], $result->errorCodes());
    }

    public function test_type_decoding_for_constructed_pair(): void
    {
        // Constructed via the documented algorithm so we hit every known type.
        $billPrefix = '12345678902'; // type digit = 2 → electric
        $billId     = $billPrefix . (string) self::mod11($billPrefix);

        $paymentPrefix = '1234';
        $first         = self::mod11($paymentPrefix);
        $second        = self::mod11($billId . $paymentPrefix . (string) $first);
        $paymentId     = $paymentPrefix . $first . $second;

        $result = BillId::validate($billId, $paymentId);
        self::assertTrue($result->isValid(), 'errors: ' . implode('; ', $result->errors()));
        $detail = $result->detail();
        self::assertInstanceOf(BillIdDetails::class, $detail);
        self::assertSame(BillType::ELECTRIC, $detail->type);
        self::assertSame($billId, $detail->billId);
    }

    public function test_unknown_type_digit_decodes_to_other(): void
    {
        // Type digit '7' isn't in the table — falls through to 'other'
        // (a documented leniency over upstream's 'unknown' rejection).
        $billPrefix = '12345678907';
        $billId     = $billPrefix . (string) self::mod11($billPrefix);

        $paymentPrefix = '1234';
        $first         = self::mod11($paymentPrefix);
        $second        = self::mod11($billId . $paymentPrefix . (string) $first);
        $paymentId     = $paymentPrefix . $first . $second;

        $result = BillId::validate($billId, $paymentId);
        self::assertTrue($result->isValid());
        $detail = $result->detail();
        self::assertInstanceOf(BillIdDetails::class, $detail);
        self::assertSame(BillType::OTHER, $detail->type);
    }

    private static function mod11(string $digits): int
    {
        $weights = [2, 3, 4, 5, 6, 7];
        $sum     = 0;
        $len     = strlen($digits);
        for ($i = 0; $i < $len; $i++) {
            $sum += (int) $digits[$len - 1 - $i] * $weights[$i % 6];
        }
        $rem = $sum % 11;

        return $rem < 2 ? 0 : 11 - $rem;
    }
}
