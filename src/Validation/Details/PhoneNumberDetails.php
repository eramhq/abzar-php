<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation\Details;

use Eram\Abzar\Validation\Operator;
use Eram\Abzar\Validation\PhoneNumberType;
use Eram\Abzar\Validation\Province;

/**
 * Parsed details for a valid Iranian phone number.
 *
 * Construct via {@see self::mobile()} or {@see self::landline()} — the direct
 * constructor is private to keep the mobile/landline variants unambiguous.
 */
final class PhoneNumberDetails implements ValidationDetail
{
    private function __construct(
        public readonly PhoneNumberType $type,
        public readonly string $normalizedLocal,
        public readonly string $normalizedE164,
        public readonly ?string $operator = null,
        public readonly ?string $areaCode = null,
        public readonly ?string $city = null,
        public readonly ?string $province = null,
    ) {
    }

    public static function mobile(string $normalizedLocal, string $normalizedE164, ?string $operator): self
    {
        return new self(
            type:            PhoneNumberType::MOBILE,
            normalizedLocal: $normalizedLocal,
            normalizedE164:  $normalizedE164,
            operator:        $operator,
        );
    }

    public static function landline(
        string $normalizedLocal,
        string $normalizedE164,
        string $areaCode,
        string $city,
        string $province,
    ): self {
        return new self(
            type:            PhoneNumberType::LANDLINE,
            normalizedLocal: $normalizedLocal,
            normalizedE164:  $normalizedE164,
            areaCode:        $areaCode,
            city:            $city,
            province:        $province,
        );
    }

    public function isMobile(): bool
    {
        return $this->type === PhoneNumberType::MOBILE;
    }

    public function isLandline(): bool
    {
        return $this->type === PhoneNumberType::LANDLINE;
    }

    public function operatorEnum(): ?Operator
    {
        return $this->operator === null ? null : Operator::fromPersian($this->operator);
    }

    public function provinceEnum(): ?Province
    {
        return $this->province === null ? null : Province::fromPersian($this->province);
    }

    /**
     * @return array{
     *     type: string,
     *     normalized_local: string,
     *     normalized_e164: string,
     *     operator?: string,
     *     area_code?: string,
     *     city?: string,
     *     province?: string
     * }
     */
    public function jsonSerialize(): array
    {
        $out = [
            'type'             => $this->type->value,
            'normalized_local' => $this->normalizedLocal,
            'normalized_e164'  => $this->normalizedE164,
        ];

        if ($this->operator !== null) {
            $out['operator'] = $this->operator;
        }
        if ($this->areaCode !== null) {
            $out['area_code'] = $this->areaCode;
        }
        if ($this->city !== null) {
            $out['city'] = $this->city;
        }
        if ($this->province !== null) {
            $out['province'] = $this->province;
        }

        return $out;
    }
}
