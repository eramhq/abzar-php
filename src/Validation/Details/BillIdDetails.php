<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation\Details;

use Eram\Abzar\Validation\BillType;

/**
 * Parsed details for a valid bill ID. {@code paymentId} is {@code null} when
 * the DTO was produced by the single-field {@see \Eram\Abzar\Validation\BillId::validate()};
 * populated by {@see \Eram\Abzar\Validation\BillId::validatePair()} / {@code ::from()}.
 */
final class BillIdDetails implements ValidationDetail
{
    public function __construct(
        public readonly string $billId,
        public readonly ?string $paymentId,
        public readonly BillType $type,
    ) {
    }

    /**
     * @return array{bill_id: string, payment_id: ?string, type: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'bill_id'    => $this->billId,
            'payment_id' => $this->paymentId,
            'type'       => $this->type->value,
        ];
    }
}
