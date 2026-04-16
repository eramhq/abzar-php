<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\AbzarValidationException;
use Eram\Abzar\Internal\ErrorInput;
use Eram\Abzar\Validation\Details\BillIdDetails;

/**
 * Bank utility bill ID ({@code شناسه قبض}) + payment ID ({@code شناسه پرداخت})
 * pair validator. Mod-11 weighting and payment cross-check verified against
 * {@link https://github.com/persian-tools/persian-tools/blob/main/src/modules/bill/index.ts}
 * on 2026-04-16.
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
     * @throws AbzarValidationException
     */
    public static function from(string $billId, string $paymentId): self
    {
        $result = self::validate($billId, $paymentId);
        if (!$result->isValid()) {
            throw AbzarValidationException::fromResult($result);
        }

        /** @var BillIdDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function tryFrom(string $billId, string $paymentId): ?self
    {
        $result = self::validate($billId, $paymentId);
        if (!$result->isValid()) {
            return null;
        }

        /** @var BillIdDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function validate(string $billId, string $paymentId): ValidationResult
    {
        $billId    = ErrorInput::digits($billId);
        $paymentId = ErrorInput::digits($paymentId);

        if ($billId === '' || $paymentId === '') {
            return ValidationResult::invalid(ErrorCode::BILL_ID_EMPTY);
        }

        if (!preg_match('/^\d{6,18}$/', $billId) || !preg_match('/^\d{6,18}$/', $paymentId)) {
            return ValidationResult::invalid(ErrorCode::BILL_ID_WRONG_LENGTH);
        }

        if (!self::checksumMatches($billId)) {
            return ValidationResult::invalid(ErrorCode::BILL_ID_INVALID_CHECKSUM);
        }

        if (!self::paymentMatches($billId, $paymentId)) {
            return ValidationResult::invalid(ErrorCode::BILL_ID_PAYMENT_MISMATCH);
        }

        return ValidationResult::valid(new BillIdDetails(
            billId:    $billId,
            paymentId: $paymentId,
            type:      BillType::fromTypeDigit((int) $billId[-2]),
        ));
    }

    public function billId(): string
    {
        return $this->detail->billId;
    }

    public function paymentId(): string
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
     * @return array{bill_id: string, payment_id: string, type: string}
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
