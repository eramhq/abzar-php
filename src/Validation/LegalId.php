<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\AbzarValidationException;
use Eram\Abzar\Digits\DigitConverter;
use Eram\Abzar\Validation\Details\LegalIdDetails;

final class LegalId implements \JsonSerializable, \Stringable
{
    private const COEFFICIENTS = [29, 27, 23, 19, 17];

    private function __construct(
        private readonly LegalIdDetails $detail,
    ) {
    }

    /**
     * @throws AbzarValidationException
     */
    public static function from(string $input): self
    {
        $result = self::validate($input);
        if (!$result->isValid()) {
            throw AbzarValidationException::fromResult($result);
        }

        /** @var LegalIdDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function tryFrom(string $input): ?self
    {
        $result = self::validate($input);
        if (!$result->isValid()) {
            return null;
        }

        /** @var LegalIdDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function validate(string $input): ValidationResult
    {
        $normalized = DigitConverter::toEnglish(trim($input));

        if ($normalized === '') {
            return ValidationResult::invalid(ErrorCode::LEGAL_ID_EMPTY);
        }

        if (!preg_match('/^\d{11}$/', $normalized)) {
            return ValidationResult::invalid(ErrorCode::LEGAL_ID_WRONG_LENGTH);
        }

        if (substr($normalized, 3, 6) === '000000') {
            return ValidationResult::invalid(ErrorCode::LEGAL_ID_MIDDLE_ZEROS);
        }

        if ((int) $normalized[10] !== self::checkDigit(substr($normalized, 0, 10))) {
            return ValidationResult::invalid(ErrorCode::LEGAL_ID_INVALID_CHECKSUM);
        }

        return ValidationResult::valid(new LegalIdDetails(value: $normalized));
    }

    /**
     * Generate a valid 11-digit Iranian legal-entity ID for fixtures or tests.
     * Named {@code fake} to discourage production use — the ID is valid by
     * construction but may or may not belong to a real entity.
     */
    public static function fake(): string
    {
        do {
            $body = '';
            for ($i = 0; $i < 10; $i++) {
                $body .= (string) random_int(0, 9);
            }
        } while (substr($body, 3, 6) === '000000');

        return $body . self::checkDigit($body);
    }

    /**
     * Weighted checksum for the 10-digit body of an Iranian legal-entity ID.
     */
    private static function checkDigit(string $tenDigits): int
    {
        $d   = (int) $tenDigits[9] + 2;
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += ($d + (int) $tenDigits[$i]) * self::COEFFICIENTS[$i % 5];
        }

        $check = $sum % 11;

        return $check === 10 ? 0 : $check;
    }

    public function value(): string
    {
        return $this->detail->value;
    }

    public function detail(): LegalIdDetails
    {
        return $this->detail;
    }

    public function __toString(): string
    {
        return $this->detail->value;
    }

    /**
     * @return array{value: string}
     */
    public function jsonSerialize(): array
    {
        return $this->detail->jsonSerialize();
    }
}
