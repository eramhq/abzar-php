<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\Data\DataSources;
use Eram\Abzar\Internal\ErrorInput;

final class PhoneNumber
{
    private function __construct()
    {
    }

    public static function validate(string $input): ValidationResult
    {
        $input = ErrorInput::digits($input, '()');

        if ($input === '') {
            return ValidationResult::failure(ErrorCode::PHONE_NUMBER_EMPTY);
        }

        if (str_starts_with($input, '+98')) {
            $input = '0' . substr($input, 3);
        } elseif (str_starts_with($input, '0098')) {
            $input = '0' . substr($input, 4);
        } elseif (str_starts_with($input, '98') && strlen($input) === 12) {
            $input = '0' . substr($input, 2);
        } elseif (preg_match('/^9\d{9}$/', $input)) {
            $input = '0' . $input;
        }

        if (preg_match('/^09\d{9}$/', $input)) {
            return self::mobileResult($input);
        }

        if (preg_match('/^0\d{10}$/', $input)) {
            $landline = self::landlineResult($input);
            if ($landline !== null) {
                return $landline;
            }
        }

        return ValidationResult::failure(ErrorCode::PHONE_NUMBER_INVALID_FORMAT);
    }

    public static function normalize(string $input): ?string
    {
        $result = self::validate($input);

        return $result->isValid() ? $result->details()['normalized_local'] : null;
    }

    private static function mobileResult(string $normalizedLocal): ValidationResult
    {
        $prefix = substr($normalizedLocal, 1, 3);

        return ValidationResult::success([
            'normalized_local' => $normalizedLocal,
            'normalized_e164'  => self::toE164($normalizedLocal),
            'operator'         => DataSources::phoneOperators()[$prefix] ?? null,
            'type'             => 'mobile',
        ]);
    }

    private static function landlineResult(string $normalizedLocal): ?ValidationResult
    {
        $areaCode  = substr($normalizedLocal, 0, 3);
        $areaCodes = DataSources::phoneAreaCodes();

        if (!isset($areaCodes[$areaCode])) {
            return null;
        }

        return ValidationResult::success([
            'normalized_local' => $normalizedLocal,
            'normalized_e164'  => self::toE164($normalizedLocal),
            'type'             => 'landline',
            'area_code'        => $areaCode,
            'province'         => $areaCodes[$areaCode]['province'],
            'city'             => $areaCodes[$areaCode]['city'],
        ]);
    }

    private static function toE164(string $normalizedLocal): string
    {
        return '+98' . substr($normalizedLocal, 1);
    }
}
