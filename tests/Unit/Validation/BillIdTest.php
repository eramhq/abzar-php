<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\Validation\BillId;
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

    public function test_valid_pair_passes_with_type_decoding(): void
    {
        // Generate a checksum-valid pair programmatically using the public algorithm.
        // Bill prefix ending in "2" (electric) so type decodes to 'electric'.
        $billPrefix = '12345678902';
        $billId     = $billPrefix . (string) self::mod11($billPrefix);

        $paymentPrefix = '1234';
        $first         = self::mod11($billId . $paymentPrefix);
        $second        = self::mod11($billId . $paymentPrefix . (string) $first);
        $paymentId     = $paymentPrefix . $first . $second;

        $result = BillId::validate($billId, $paymentId);

        self::assertTrue($result->isValid(), 'errors: ' . implode('; ', $result->errors()));
        self::assertSame('electric', $result->details()['type']);
        self::assertSame($billId, $result->details()['bill_id']);
    }

    public function test_payment_id_mismatch_fails(): void
    {
        $billPrefix = '12345678902';
        $billId     = $billPrefix . (string) self::mod11($billPrefix);

        // Use deliberately wrong payment last-two digits.
        $paymentId = '123499';

        $result = BillId::validate($billId, $paymentId);
        self::assertFalse($result->isValid());
        self::assertSame([ErrorCode::BILL_ID_PAYMENT_MISMATCH], $result->errorCodes());
    }

    public function test_unknown_type_digit_decodes_to_other(): void
    {
        // Type digit '7' isn't in the table — should fall through to 'other'.
        $billPrefix = '12345678907';
        $billId     = $billPrefix . (string) self::mod11($billPrefix);

        $paymentPrefix = '1234';
        $first         = self::mod11($billId . $paymentPrefix);
        $second        = self::mod11($billId . $paymentPrefix . (string) $first);
        $paymentId     = $paymentPrefix . $first . $second;

        $result = BillId::validate($billId, $paymentId);
        self::assertTrue($result->isValid());
        self::assertSame('other', $result->details()['type']);
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
