<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\AbzarValidationException;
use Eram\Abzar\Digits\DigitConverter;

final class LegalId implements \JsonSerializable, \Stringable
{
    private const COEFFICIENTS = [29, 27, 23, 19, 17];

    private function __construct(
        private readonly string $value,
    ) {
    }

    /**
     * @throws AbzarValidationException
     */
    public static function from(string $input): self
    {
        $normalized = self::normalize($input);
        $result     = self::validateNormalized($normalized);
        if (!$result->isValid()) {
            throw AbzarValidationException::fromResult($result);
        }

        return new self($normalized);
    }

    public static function tryFrom(string $input): ?self
    {
        $normalized = self::normalize($input);
        if (!self::validateNormalized($normalized)->isValid()) {
            return null;
        }

        return new self($normalized);
    }

    public static function validate(string $input): ValidationResult
    {
        return self::validateNormalized(self::normalize($input));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @return array{value: string}
     */
    public function jsonSerialize(): array
    {
        return ['value' => $this->value];
    }

    private static function normalize(string $input): string
    {
        return DigitConverter::toEnglish(trim($input));
    }

    private static function validateNormalized(string $input): ValidationResult
    {
        if ($input === '') {
            return ValidationResult::invalid(ErrorCode::LEGAL_ID_EMPTY);
        }

        if (!preg_match('/^\d{11}$/', $input)) {
            return ValidationResult::invalid(ErrorCode::LEGAL_ID_WRONG_LENGTH);
        }

        $digits = array_map('intval', str_split($input));

        $middle = array_slice($digits, 3, 6);
        if (array_sum($middle) === 0) {
            return ValidationResult::invalid(ErrorCode::LEGAL_ID_MIDDLE_ZEROS);
        }

        $d   = $digits[9] + 2;
        $sum = 0;

        for ($i = 0; $i < 10; $i++) {
            $sum += ($d + $digits[$i]) * self::COEFFICIENTS[$i % 5];
        }

        $checksum = $sum % 11;
        if ($checksum === 10) {
            $checksum = 0;
        }

        if ($digits[10] !== $checksum) {
            return ValidationResult::invalid(ErrorCode::LEGAL_ID_INVALID_CHECKSUM);
        }

        return ValidationResult::valid();
    }
}
