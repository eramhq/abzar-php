<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\Data\DataSources;
use Eram\Abzar\Internal\ErrorInput;

final class NationalId
{
    private function __construct()
    {
    }

    public static function validate(string $input): ValidationResult
    {
        $input = ErrorInput::digits($input);

        if ($input === '') {
            return ValidationResult::failure(ErrorCode::NATIONAL_ID_EMPTY);
        }

        // Left-pad to 10 digits for 8-9 digit inputs
        if (strlen($input) >= 8 && strlen($input) < 10) {
            $input = str_pad($input, 10, '0', STR_PAD_LEFT);
        }

        if (!preg_match('/^\d{10}$/', $input)) {
            return ValidationResult::failure(ErrorCode::NATIONAL_ID_WRONG_LENGTH);
        }

        if (preg_match('/^(\d)\1{9}$/', $input)) {
            return ValidationResult::failure(ErrorCode::NATIONAL_ID_ALL_SAME_DIGITS);
        }

        if ($input === '0123456789') {
            return ValidationResult::failure(ErrorCode::NATIONAL_ID_SEQUENTIAL_DIGITS);
        }

        if (substr($input, 3, 6) === '000000') {
            return ValidationResult::failure(ErrorCode::NATIONAL_ID_MIDDLE_ZEROS);
        }

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $input[$i] * (10 - $i);
        }

        $remainder = $sum % 11;
        $checkDigit = (int) $input[9];

        if ($remainder < 2) {
            $valid = $checkDigit === $remainder;
        } else {
            $valid = $checkDigit === (11 - $remainder);
        }

        if (!$valid) {
            return ValidationResult::failure(ErrorCode::NATIONAL_ID_INVALID_CHECKSUM);
        }

        $prefix    = substr($input, 0, 3);
        $cityData  = DataSources::nationalIdCityCodes()[$prefix] ?? ['city' => null, 'province' => null];

        return ValidationResult::success([
            'city_code' => $prefix,
            'city'      => $cityData['city'],
            'province'  => $cityData['province'],
        ]);
    }
}
