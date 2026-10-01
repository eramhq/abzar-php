<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation\Details;

use Eram\Abzar\Validation\PlateType;
use Eram\Abzar\Validation\Province;

/**
 * Parsed components of an Iranian license plate. The canonical shape is
 * {@code NN[letter]NNN-NN}: {@code twoDigit} + {@code letter} + {@code threeDigit}
 * + {@code cityCode}. {@code type} is the letter-derived category.
 *
 * {@code provinces} lists every province the city code was issued in — usually
 * one, but codes issued before a province split (e.g. 21 → Tehran + Alborz)
 * list each successor. {@code province} is the display form: the single name,
 * the names joined with {@code " - "}, or {@code null} when the code isn't in
 * the table.
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
        /** @var list<string> */
        public readonly array $provinces = [],
    ) {
    }

    public function provinceEnum(): ?Province
    {
        return count($this->provinces) === 1 ? Province::fromPersian($this->provinces[0]) : null;
    }

    /**
     * @return list<Province>
     */
    public function provinceEnums(): array
    {
        $out = [];
        foreach ($this->provinces as $name) {
            $province = Province::fromPersian($name);
            if ($province !== null) {
                $out[] = $province;
            }
        }

        return $out;
    }

    /**
     * @return array{
     *     two_digit: string,
     *     letter: string,
     *     three_digit: string,
     *     city_code: string,
     *     type: string,
     *     province: ?string,
     *     provinces: list<string>
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
            'provinces'   => $this->provinces,
        ];
    }
}
