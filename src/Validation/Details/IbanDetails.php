<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation\Details;

use Eram\Abzar\Validation\Bank;

final class IbanDetails implements \JsonSerializable
{
    public function __construct(
        public readonly string $value,
        public readonly string $bankCode,
        public readonly ?string $bank,
    ) {
    }

    public function bankEnum(): ?Bank
    {
        return $this->bank === null ? null : Bank::fromPersian($this->bank);
    }

    /**
     * @return array{value: string, bank_code: string, bank: ?string}
     */
    public function jsonSerialize(): array
    {
        return [
            'value'     => $this->value,
            'bank_code' => $this->bankCode,
            'bank'      => $this->bank,
        ];
    }
}
