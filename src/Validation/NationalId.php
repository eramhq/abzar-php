<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\AbzarValidationException;
use Eram\Abzar\Data\DataSources;
use Eram\Abzar\Internal\ErrorInput;
use Eram\Abzar\Validation\Details\NationalIdDetails;

/**
 * Iranian National ID (کد ملی) — a 10-digit personal identifier. Ingest
 * via {@see self::from()} or {@see self::tryFrom()} when the input should
 * become a handle; use {@see self::validate()} for a plain pass/fail check.
 */
final class NationalId implements \JsonSerializable, \Stringable
{
    private function __construct(
        private readonly NationalIdDetails $detail,
    ) {
    }

    /**
     * @throws AbzarValidationException when the input is not a valid national ID.
     */
    public static function from(string $input): self
    {
        $result = self::validate($input);
        if (!$result->isValid()) {
            throw AbzarValidationException::fromResult($result);
        }

        /** @var NationalIdDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function tryFrom(string $input): ?self
    {
        $result = self::validate($input);
        if (!$result->isValid()) {
            return null;
        }

        /** @var NationalIdDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function validate(string $input): ValidationResult
    {
        $input = ErrorInput::digits($input);

        if ($input === '') {
            return ValidationResult::invalid(ErrorCode::NATIONAL_ID_EMPTY);
        }

        $len = strlen($input);
        if ($len === 8 || $len === 9) {
            $input = str_pad($input, 10, '0', STR_PAD_LEFT);
        }

        if (!preg_match('/^\d{10}$/', $input)) {
            return ValidationResult::invalid(ErrorCode::NATIONAL_ID_WRONG_LENGTH);
        }

        if (preg_match('/^(\d)\1{9}$/', $input)) {
            return ValidationResult::invalid(ErrorCode::NATIONAL_ID_ALL_SAME_DIGITS);
        }

        if ($input === '0123456789') {
            return ValidationResult::invalid(ErrorCode::NATIONAL_ID_SEQUENTIAL_DIGITS);
        }

        if (substr($input, 3, 6) === '000000') {
            return ValidationResult::invalid(ErrorCode::NATIONAL_ID_MIDDLE_ZEROS);
        }

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $input[$i] * (10 - $i);
        }

        $remainder  = $sum % 11;
        $checkDigit = (int) $input[9];
        $valid      = $remainder < 2 ? $checkDigit === $remainder : $checkDigit === (11 - $remainder);

        if (!$valid) {
            return ValidationResult::invalid(ErrorCode::NATIONAL_ID_INVALID_CHECKSUM);
        }

        $prefix   = substr($input, 0, 3);
        $cityData = DataSources::nationalIdCityCodes()[$prefix] ?? ['city' => null, 'province' => null];

        return ValidationResult::valid(new NationalIdDetails(
            value:    $input,
            cityCode: $prefix,
            city:     $cityData['city'],
            province: $cityData['province'],
        ));
    }

    public function value(): string
    {
        return $this->detail->value;
    }

    public function cityCode(): string
    {
        return $this->detail->cityCode;
    }

    public function city(): ?string
    {
        return $this->detail->city;
    }

    public function province(): ?string
    {
        return $this->detail->province;
    }

    public function provinceEnum(): ?Province
    {
        return $this->detail->provinceEnum();
    }

    public function detail(): NationalIdDetails
    {
        return $this->detail;
    }

    public function __toString(): string
    {
        return $this->detail->value;
    }

    /**
     * @return array{value: string, city_code: string, city: ?string, province: ?string}
     */
    public function jsonSerialize(): array
    {
        return $this->detail->jsonSerialize();
    }
}
