<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation\Details;

use Eram\Abzar\Validation\PlateType;

/**
 * Parsed components of an Iranian license plate. The canonical shape is
 * {@code NN[letter]NNN-NN}: {@code twoDigit} + {@code letter} + {@code threeDigit}
 * + {@code cityCode}. {@code type} is the letter-derived category; {@code province}
 * is the city-code lookup result ({@code null} when the code isn't in the table).
 */
final class PlateNumberDetails implements ValidationDetail
{
    public function __construct(
        public readonly string $twoDigit,
        public readonly string $letter,
        public readonly string $threeDigit,
        public readonly string $cityCode,
        public readonly PlateType $type,
        public readonly ?string $province,
    ) {
    }

    /**
     * @return array{
     *     two_digit: string,
     *     letter: string,
     *     three_digit: string,
     *     city_code: string,
     *     type: string,
     *     province: ?string
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'two_digit'   => $this->twoDigit,
            'letter'      => $this->letter,
            'three_digit' => $this->threeDigit,
            'city_code'   => $this->cityCode,
            'type'        => $this->type->value,
            'province'    => $this->province,
        ];
    }
}
