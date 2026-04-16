<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\Data\DataSources;
use Eram\Abzar\Internal\ErrorInput;

final class CardNumber
{
    private function __construct()
    {
    }

    public static function validate(string $input): ValidationResult
    {
        $input = ErrorInput::digits($input);

        if ($input === '') {
            return ValidationResult::failure(ErrorCode::CARD_NUMBER_EMPTY);
        }

        if (!preg_match('/^\d{16}$/', $input)) {
            return ValidationResult::failure(ErrorCode::CARD_NUMBER_WRONG_LENGTH);
        }

        $bin   = substr($input, 0, 6);
        $banks = DataSources::cardBanks();
        if (!isset($banks[$bin])) {
            return ValidationResult::failure(ErrorCode::CARD_NUMBER_INVALID_CHECKSUM);
        }

        if (!self::luhn($input)) {
            return ValidationResult::failure(ErrorCode::CARD_NUMBER_INVALID_CHECKSUM);
        }

        return ValidationResult::success([
            'bank' => $banks[$bin],
            'bin'  => $bin,
        ]);
    }

    private static function luhn(string $number): bool
    {
        $sum    = 0;
        $length = strlen($number);

        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $number[$length - 1 - $i];

            if ($i % 2 === 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return $sum % 10 === 0;
    }
}
