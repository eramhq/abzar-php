<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation\Details;

use Eram\Abzar\Validation\Bank;

final class CardNumberDetails implements \JsonSerializable
{
    public function __construct(
        public readonly string $value,
        public readonly string $bin,
        public readonly string $bank,
    ) {
    }

    public function bankEnum(): ?Bank
    {
        return Bank::fromPersian($this->bank);
    }

    /**
     * @return array{value: string, bin: string, bank: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
            'bin'   => $this->bin,
            'bank'  => $this->bank,
        ];
    }
}
