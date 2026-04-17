<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation\Details;

final class LegalIdDetails implements ValidationDetail
{
    public function __construct(
        public readonly string $value,
    ) {
    }

    /**
     * @return array{value: string}
     */
    public function jsonSerialize(): array
    {
        return ['value' => $this->value];
    }
}
