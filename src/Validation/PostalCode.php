<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\Exception\ValidationException;
use Eram\Abzar\Internal\ErrorInput;
use Eram\Abzar\Validation\Details\PostalCodeDetails;

/**
 * Iranian 10-digit postal code validator. Rules mirror persian-tools v5:
 * first digit ≠ 0, fifth digit ≠ 0, no run of 4+ identical digits anywhere.
 */
final class PostalCode implements \JsonSerializable, \Stringable
{
    private function __construct(
        private readonly PostalCodeDetails $detail,
    ) {
    }

    /**
     * @throws ValidationException
     */
    public static function from(string $input): self
    {
        $result = self::validate($input);
        if (!$result->isValid()) {
            throw ValidationException::fromResult($result);
        }

        /** @var PostalCodeDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function tryFrom(string $input): ?self
    {
        $result = self::validate($input);
        if (!$result->isValid()) {
            return null;
        }

        /** @var PostalCodeDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function validate(string $input): ValidationResult
    {
        $input = ErrorInput::digits($input);

        if ($input === '') {
            return ValidationResult::invalid(ErrorCode::POSTAL_CODE_EMPTY);
        }

        if (!preg_match('/^\d{10}$/', $input)) {
            return ValidationResult::invalid(ErrorCode::POSTAL_CODE_WRONG_LENGTH);
        }

        if ($input[0] === '0' || $input[4] === '0' || preg_match('/(\d)\1{3}/', $input)) {
            return ValidationResult::invalid(ErrorCode::POSTAL_CODE_INVALID_PATTERN);
        }

        return ValidationResult::valid(new PostalCodeDetails(
            postalCode: $input,
            zoneCode:   substr($input, 0, 5),
        ));
    }

    /**
     * Generate a valid 10-digit Iranian postal code for fixtures or tests.
     * Retries until the random digits satisfy the validator's pattern rules
     * (first digit ≠ 0, fifth digit ≠ 0, no run of 4+ identical digits).
     * Named {@code fake} to discourage production use — the code is valid by
     * construction but may not correspond to a real address.
     */
    public static function fake(): string
    {
        while (true) {
            $code = (string) random_int(1, 9);
            for ($i = 1; $i < 4; $i++) {
                $code .= (string) random_int(0, 9);
            }
            $code .= (string) random_int(1, 9);
            for ($i = 5; $i < 10; $i++) {
                $code .= (string) random_int(0, 9);
            }

            if (!preg_match('/(\d)\1{3}/', $code)) {
                return $code;
            }
        }
    }

    public function value(): string
    {
        return $this->detail->postalCode;
    }

    public function zoneCode(): string
    {
        return $this->detail->zoneCode;
    }

    public function detail(): PostalCodeDetails
    {
        return $this->detail;
    }

    public function __toString(): string
    {
        return $this->detail->postalCode;
    }

    /**
     * @return array{postal_code: string, zone_code: string}
     */
    public function jsonSerialize(): array
    {
        return $this->detail->jsonSerialize();
    }
}
