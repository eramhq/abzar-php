<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\Digits\DigitConverter;

final class LegalId
{
    private const COEFFICIENTS = [29, 27, 23, 19, 17];

    private function __construct()
    {
    }

    public static function validate(string $input): ValidationResult
    {
        $input = DigitConverter::toEnglish(trim($input));

        if ($input === '') {
            return ValidationResult::failure(ErrorCode::LEGAL_ID_EMPTY);
        }

        if (!preg_match('/^\d{11}$/', $input)) {
            return ValidationResult::failure(ErrorCode::LEGAL_ID_WRONG_LENGTH);
        }

        $digits = array_map('intval', str_split($input));

        // Middle 6 digits (positions 3-8) must not all be zero
        $middle = array_slice($digits, 3, 6);
        if (array_sum($middle) === 0) {
            return ValidationResult::failure(ErrorCode::LEGAL_ID_MIDDLE_ZEROS);
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
            return ValidationResult::failure(ErrorCode::LEGAL_ID_INVALID_CHECKSUM);
        }

        return ValidationResult::success();
    }
}
