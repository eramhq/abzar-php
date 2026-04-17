<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation\Details;

final class PostalCodeDetails implements ValidationDetail
{
    public function __construct(
        public readonly string $postalCode,
        public readonly string $zoneCode,
    ) {
    }

    /**
     * @return array{postal_code: string, zone_code: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'postal_code' => $this->postalCode,
            'zone_code'   => $this->zoneCode,
        ];
    }
}
