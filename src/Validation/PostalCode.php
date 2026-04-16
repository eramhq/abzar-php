<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\Internal\ErrorInput;

/**
 * Iranian 10-digit postal code validator. Rules mirror persian-tools v5:
 * first digit ≠ 0, fifth digit ≠ 0, no run of 4+ identical digits anywhere.
 */
final class PostalCode
{
    private function __construct()
    {
    }

    public static function validate(string $input): ValidationResult
    {
        $input = ErrorInput::digits($input);

        if ($input === '') {
            return ValidationResult::failure(ErrorCode::POSTAL_CODE_EMPTY);
        }

        if (!preg_match('/^\d{10}$/', $input)) {
            return ValidationResult::failure(ErrorCode::POSTAL_CODE_WRONG_LENGTH);
        }

        if ($input[0] === '0' || $input[4] === '0' || preg_match('/(\d)\1{3}/', $input)) {
            return ValidationResult::failure(ErrorCode::POSTAL_CODE_INVALID_PATTERN);
        }

        return ValidationResult::success([
            'postal_code' => $input,
            'zone_code'   => substr($input, 0, 5),
            'district'    => null,
        ]);
    }
}
