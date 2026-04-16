<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation\Details;

use Eram\Abzar\Validation\BillType;

final class BillIdDetails implements \JsonSerializable
{
    public function __construct(
        public readonly string $billId,
        public readonly string $paymentId,
        public readonly BillType $type,
    ) {
    }

    /**
     * @return array{bill_id: string, payment_id: string, type: string}
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
