<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\AbzarValidationException;
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
     * @throws AbzarValidationException
     */
    public static function from(string $input): self
    {
        $result = self::validate($input);
        if (!$result->isValid()) {
            throw AbzarValidationException::fromResult($result);
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
