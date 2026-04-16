<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation\Details;

use Eram\Abzar\Validation\Province;

/**
 * Parsed details for a valid Iranian national ID.
 *
 * {@code cityCode} is the 3-digit prefix used to look up birthplace.
 * {@code city} and {@code province} are {@code null} when the prefix is unassigned
 * or otherwise missing from the bundled lookup table.
 */
final class NationalIdDetails implements \JsonSerializable
{
    public function __construct(
        public readonly string $value,
        public readonly string $cityCode,
        public readonly ?string $city,
        public readonly ?string $province,
    ) {
    }

    public function provinceEnum(): ?Province
    {
        return $this->province === null ? null : Province::fromPersian($this->province);
    }

    /**
     * @return array{value: string, city_code: string, city: ?string, province: ?string}
     */
    public function jsonSerialize(): array
    {
        return [
            'value'     => $this->value,
            'city_code' => $this->cityCode,
            'city'      => $this->city,
            'province'  => $this->province,
        ];
    }
}
