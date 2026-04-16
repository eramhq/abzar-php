<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\Data\DataSources;
use Eram\Abzar\Digits\DigitConverter;

final class Iban
{
    private function __construct()
    {
    }

    public static function validate(string $input): ValidationResult
    {
        $input = DigitConverter::toEnglish(trim($input));
        $input = strtoupper((string) preg_replace('/\s/', '', $input));

        if ($input === '') {
            return ValidationResult::failure(ErrorCode::IBAN_EMPTY);
        }

        // Auto-prepend IR for 24-digit input
        if (preg_match('/^\d{24}$/', $input)) {
            $input = 'IR' . $input;
        }

        if (!preg_match('/^IR\d{24}$/', $input)) {
            if (preg_match('/^[A-Z]{2}/', $input) && !str_starts_with($input, 'IR')) {
                return ValidationResult::failure(ErrorCode::IBAN_MISSING_PREFIX);
            }
            return ValidationResult::failure(ErrorCode::IBAN_WRONG_LENGTH);
        }

        if (!self::mod97($input)) {
            return ValidationResult::failure(ErrorCode::IBAN_INVALID_CHECKSUM);
        }

        $bankCode = substr($input, 4, 3);
        $bank     = DataSources::ibanBanks()[$bankCode] ?? null;

        return ValidationResult::success([
            'bank_code' => $bankCode,
            'bank'      => $bank,
        ]);
    }

    private static function mod97(string $iban): bool
    {
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);

        $numeric = '';
        for ($i = 0; $i < strlen($rearranged); $i++) {
            $char = $rearranged[$i];
            $numeric .= ctype_alpha($char) ? (string) (ord($char) - ord('A') + 10) : $char;
        }

        $remainder = '';
        for ($i = 0; $i < strlen($numeric); $i++) {
            $remainder .= $numeric[$i];
            $remainder = (string) ((int) $remainder % 97);
        }

        return (int) $remainder === 1;
    }
}
