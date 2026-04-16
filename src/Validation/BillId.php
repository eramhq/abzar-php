<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\Internal\ErrorInput;

/**
 * Bank utility bill ID ({@code شناسه قبض}) + payment ID ({@code شناسه پرداخت})
 * pair validator. Mod-11 weighting from persian-tools v5 / pec.ir; see
 * {@link https://github.com/persian-tools/persian-tools}.
 */
final class BillId
{
    private const WEIGHTS     = [2, 3, 4, 5, 6, 7];
    private const WEIGHTS_LEN = 6;

    private const TYPES = [
        1 => 'water',
        2 => 'electric',
        3 => 'gas',
        4 => 'phone',
        5 => 'mobile',
        6 => 'tax',
        8 => 'services',
        9 => 'passport',
    ];

    private function __construct()
    {
    }

    public static function validate(string $billId, string $paymentId): ValidationResult
    {
        $billId    = ErrorInput::digits($billId);
        $paymentId = ErrorInput::digits($paymentId);

        if ($billId === '' || $paymentId === '') {
            return ValidationResult::failure(ErrorCode::BILL_ID_EMPTY);
        }

        if (!preg_match('/^\d{6,18}$/', $billId) || !preg_match('/^\d{6,18}$/', $paymentId)) {
            return ValidationResult::failure(ErrorCode::BILL_ID_WRONG_LENGTH);
        }

        if (!self::checksumMatches($billId)) {
            return ValidationResult::failure(ErrorCode::BILL_ID_INVALID_CHECKSUM);
        }

        if (!self::paymentMatches($billId, $paymentId)) {
            return ValidationResult::failure(ErrorCode::BILL_ID_PAYMENT_MISMATCH);
        }

        $typeDigit = (int) $billId[strlen($billId) - 2];
        $type      = self::TYPES[$typeDigit] ?? 'other';

        return ValidationResult::success([
            'bill_id'    => $billId,
            'payment_id' => $paymentId,
            'type'       => $type,
        ]);
    }

    private static function checksumMatches(string $digits): bool
    {
        $prefix   = substr($digits, 0, -1);
        $check    = (int) $digits[strlen($digits) - 1];

        return self::mod11($prefix) === $check;
    }

    private static function paymentMatches(string $billId, string $paymentId): bool
    {
        $paymentPrefix = substr($paymentId, 0, -2);
        $first         = (int) $paymentId[strlen($paymentId) - 2];
        $second        = (int) $paymentId[strlen($paymentId) - 1];

        $expectedFirst  = self::mod11($billId . $paymentPrefix);
        $expectedSecond = self::mod11($billId . $paymentPrefix . (string) $first);

        return $expectedFirst === $first && $expectedSecond === $second;
    }

    private static function mod11(string $digits): int
    {
        $sum = 0;
        $len = strlen($digits);
        for ($i = 0; $i < $len; $i++) {
            $sum += (int) $digits[$len - 1 - $i] * self::WEIGHTS[$i % self::WEIGHTS_LEN];
        }

        $rem = $sum % 11;

        return $rem < 2 ? 0 : 11 - $rem;
    }
}
