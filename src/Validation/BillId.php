<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\Exception\ValidationException;
use Eram\Abzar\Internal\ErrorInput;
use Eram\Abzar\Validation\Details\BillIdDetails;

/**
 * Bank utility bill ID ({@code شناسه قبض}) validator plus the optional
 * {@code شناسه پرداخت} cross-checksum. Mod-11 weighting and payment cross-check
 * verified against {@link https://github.com/persian-tools/persian-tools/blob/main/src/modules/bill/index.ts}
 * on 2026-04-16.
 *
 * Two entry points for the {@code ::validate} surface:
 *  * {@see self::validate()} — single-field: just the bill ID. Many banking
 *    systems store only the bill ID and surface the payment ID elsewhere.
 *  * {@see self::validatePair()} — both halves, with cross-checksum.
 *
 * Unknown type-digits (0, 7) decode to {@see BillType::OTHER} rather than
 * rejecting — a documented leniency over the upstream JS library.
 */
final class BillId implements \JsonSerializable, \Stringable
{
    private const WEIGHTS = [2, 3, 4, 5, 6, 7];

    private function __construct(
        private readonly BillIdDetails $detail,
    ) {
    }

    /**
     * @throws ValidationException
     */
    public static function from(string $billId, string $paymentId): self
    {
        $result = self::validatePair($billId, $paymentId);
        if (!$result->isValid()) {
            throw ValidationException::fromResult($result);
        }

        /** @var BillIdDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function tryFrom(string $billId, string $paymentId): ?self
    {
        $result = self::validatePair($billId, $paymentId);
        if (!$result->isValid()) {
            return null;
        }

        /** @var BillIdDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function validate(string $billId): ValidationResult
    {
        $billId = ErrorInput::digits($billId);

        if ($billId === '') {
            return ValidationResult::invalid(ErrorCode::BILL_ID_EMPTY);
        }

        if (!preg_match('/^\d{6,13}$/', $billId)) {
            return ValidationResult::invalid(ErrorCode::BILL_ID_WRONG_LENGTH);
        }

        if (!self::checksumMatches($billId)) {
            return ValidationResult::invalid(ErrorCode::BILL_ID_INVALID_CHECKSUM);
        }

        return ValidationResult::valid(new BillIdDetails(
            billId:    $billId,
            paymentId: null,
            type:      BillType::fromTypeDigit((int) $billId[-2]),
        ));
    }

    public static function validatePair(string $billId, string $paymentId): ValidationResult
    {
        $paymentId = ErrorInput::digits($paymentId);

        $result = self::validate($billId);
        if (!$result->isValid()) {
            return $result;
        }

        if ($paymentId === '') {
            return ValidationResult::invalid(ErrorCode::BILL_ID_PAYMENT_EMPTY);
        }

        if (!preg_match('/^\d{6,18}$/', $paymentId)) {
            return ValidationResult::invalid(ErrorCode::BILL_ID_PAYMENT_WRONG_LENGTH);
        }

        /** @var BillIdDetails $billDetail */
        $billDetail = $result->detail();

        if (!self::paymentMatches($billDetail->billId, $paymentId)) {
            return ValidationResult::invalid(ErrorCode::BILL_ID_PAYMENT_MISMATCH);
        }

        return ValidationResult::valid(new BillIdDetails(
            billId:    $billDetail->billId,
            paymentId: $paymentId,
            type:      $billDetail->type,
        ));
    }

    /**
     * Generate a checksum-valid bill ID (13 digits: file ID, company code,
     * type digit, check digit) for fixtures or tests. Pin the bill category
     * with $type; {@see BillType::OTHER} is rejected. Pair it with
     * {@see self::fakePaymentId()} to build a full {@see self::from()} input.
     *
     * @throws ValidationException for {@see BillType::OTHER}.
     */
    public static function fake(?BillType $type = null): string
    {
        if ($type === BillType::OTHER) {
            throw ValidationException::forFakeArgument('BillType::OTHER cannot be pinned; it represents unknown type digits');
        }

        $typeDigits = [];
        for ($d = 0; $d <= 9; $d++) {
            $decoded = BillType::fromTypeDigit($d);
            if ($decoded !== BillType::OTHER && ($type === null || $decoded === $type)) {
                $typeDigits[] = $d;
            }
        }

        $body = (string) random_int(1, 9);
        for ($i = 0; $i < 10; $i++) {
            $body .= (string) random_int(0, 9);
        }
        $body .= (string) $typeDigits[array_rand($typeDigits)];

        return $body . self::mod11($body);
    }

    /**
     * Generate a payment ID (amount in thousands of rials, year digit, period,
     * two check digits) that cross-validates against $billId.
     *
     * @throws ValidationException when $billId itself is invalid.
     */
    public static function fakePaymentId(string $billId): string
    {
        $result = self::validate($billId);
        if (!$result->isValid()) {
            throw ValidationException::fromResult($result);
        }

        /** @var BillIdDetails $detail */
        $detail = $result->detail();

        $prefix = random_int(1, 99_999) . random_int(0, 9) . str_pad((string) random_int(1, 12), 2, '0', STR_PAD_LEFT);
        $first  = self::mod11($prefix);

        return $prefix . $first . self::mod11($detail->billId . $prefix . $first);
    }

    public function billId(): string
    {
        return $this->detail->billId;
    }

    public function paymentId(): ?string
    {
        return $this->detail->paymentId;
    }

    public function type(): BillType
    {
        return $this->detail->type;
    }

    public function detail(): BillIdDetails
    {
        return $this->detail;
    }

    public function __toString(): string
    {
        return $this->detail->billId;
    }

    /**
     * @return array{bill_id: string, payment_id: ?string, type: string}
     */
    public function jsonSerialize(): array
    {
        return $this->detail->jsonSerialize();
    }

    private static function checksumMatches(string $digits): bool
    {
        return self::mod11(substr($digits, 0, -1)) === (int) substr($digits, -1);
    }

    private static function paymentMatches(string $billId, string $paymentId): bool
    {
        $paymentPrefix = substr($paymentId, 0, -2);
        $first         = (int) $paymentId[-2];
        $second        = (int) $paymentId[-1];

        $expectedFirst  = self::mod11($paymentPrefix);
        $expectedSecond = self::mod11($billId . $paymentPrefix . (string) $first);

        return $expectedFirst === $first && $expectedSecond === $second;
    }

    private static function mod11(string $digits): int
    {
        $sum        = 0;
        $len        = strlen($digits);
        $weightsLen = count(self::WEIGHTS);
        for ($i = 0; $i < $len; $i++) {
            $sum += (int) $digits[$len - 1 - $i] * self::WEIGHTS[$i % $weightsLen];
        }

        $rem = $sum % 11;

        return $rem < 2 ? 0 : 11 - $rem;
    }
}
