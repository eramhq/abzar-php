<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\Exception\ValidationException;
use Eram\Abzar\Data\DataSources;
use Eram\Abzar\Internal\ErrorInput;
use Eram\Abzar\Validation\Details\NationalIdDetails;

/**
 * Iranian National ID (کد ملی) — a 10-digit personal identifier. Ingest
 * via {@see self::from()} or {@see self::tryFrom()} when the input should
 * become a handle; use {@see self::validate()} for a plain pass/fail check.
 */
final class NationalId implements \JsonSerializable, \Stringable
{
    private function __construct(
        private readonly NationalIdDetails $detail,
    ) {
    }

    /**
     * @throws ValidationException when the input is not a valid national ID.
     */
    public static function from(string $input): self
    {
        $result = self::validate($input);
        if (!$result->isValid()) {
            throw ValidationException::fromResult($result);
        }

        /** @var NationalIdDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function tryFrom(string $input): ?self
    {
        $result = self::validate($input);
        if (!$result->isValid()) {
            return null;
        }

        /** @var NationalIdDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function validate(string $input): ValidationResult
    {
        $input = ErrorInput::digits($input);

        if ($input === '') {
            return ValidationResult::invalid(ErrorCode::NATIONAL_ID_EMPTY);
        }

        $len = strlen($input);
        if (($len === 8 || $len === 9) && ctype_digit($input)) {
            // IDs with a leading zero commonly get truncated by integer round-trips
            // (CSV, Excel, intval). Auto-padding would hide that upstream bug, so we
            // reject and let the caller decide whether to str_pad before retrying.
            return ValidationResult::invalid(ErrorCode::NATIONAL_ID_LIKELY_TRUNCATED);
        }

        if (!preg_match('/^\d{10}$/', $input)) {
            return ValidationResult::invalid(ErrorCode::NATIONAL_ID_WRONG_LENGTH);
        }

        if (preg_match('/^(\d)\1{9}$/', $input)) {
            return ValidationResult::invalid(ErrorCode::NATIONAL_ID_ALL_SAME_DIGITS);
        }

        if ($input === '0123456789') {
            return ValidationResult::invalid(ErrorCode::NATIONAL_ID_SEQUENTIAL_DIGITS);
        }

        if (substr($input, 3, 6) === '000000') {
            return ValidationResult::invalid(ErrorCode::NATIONAL_ID_MIDDLE_ZEROS);
        }

        if ((int) $input[9] !== self::checkDigit(substr($input, 0, 9))) {
            return ValidationResult::invalid(ErrorCode::NATIONAL_ID_INVALID_CHECKSUM);
        }

        $prefix   = substr($input, 0, 3);
        $cityData = DataSources::nationalIdCityCodes()[$prefix] ?? ['city' => null, 'province' => null];

        return ValidationResult::valid(new NationalIdDetails(
            value:    $input,
            cityCode: $prefix,
            city:     $cityData['city'],
            province: $cityData['province'],
        ));
    }

    /**
     * Generate a Luhn-valid Iranian national ID for fixtures or tests.
     *
     * Pass {@code $cityCode} to pin the 3-digit prefix to a specific city; the
     * remaining 7 digits are random. Named {@code fake} (not {@code generate})
     * to discourage accidental use in production — the returned ID is valid
     * by construction but may or may not belong to a real person.
     */
    public static function fake(?string $cityCode = null): string
    {
        $cityCode ??= str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
        if (!preg_match('/^\d{3}$/', $cityCode)) {
            throw new \InvalidArgumentException('cityCode must be exactly 3 digits');
        }

        while (true) {
            $body = $cityCode;
            for ($i = 0; $i < 6; $i++) {
                $body .= (string) random_int(0, 9);
            }
            if (substr($body, 3, 6) === '000000') {
                continue;
            }

            $candidate = $body . self::checkDigit($body);
            if (preg_match('/^(\d)\1{9}$/', $candidate) !== 1 && $candidate !== '0123456789') {
                return $candidate;
            }
        }
    }

    /**
     * Mod-11 weighted check digit for the 9-digit body of an Iranian national ID.
     */
    private static function checkDigit(string $nineDigits): int
    {
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $nineDigits[$i] * (10 - $i);
        }
        $remainder = $sum % 11;

        return $remainder < 2 ? $remainder : 11 - $remainder;
    }

    /**
     * Scan free text for 10-digit runs and return each that parses as a valid
     * national ID. Run order follows left-to-right appearance.
     *
     * @return list<self>
     */
    public static function extractAll(string $text): array
    {
        $english = \Eram\Abzar\Digits\DigitConverter::toEnglish($text);
        preg_match_all('/(?<!\d)\d{10}(?!\d)/', $english, $matches);

        $out = [];
        foreach ($matches[0] as $candidate) {
            $vo = self::tryFrom($candidate);
            if ($vo !== null) {
                $out[] = $vo;
            }
        }

        return $out;
    }

    public function value(): string
    {
        return $this->detail->value;
    }

    public function cityCode(): string
    {
        return $this->detail->cityCode;
    }

    public function city(): ?string
    {
        return $this->detail->city;
    }

    public function province(): ?string
    {
        return $this->detail->province;
    }

    public function provinceEnum(): ?Province
    {
        return $this->detail->provinceEnum();
    }

    public function detail(): NationalIdDetails
    {
        return $this->detail;
    }

    public function __toString(): string
    {
        return $this->detail->value;
    }

    /**
     * @return array{value: string, city_code: string, city: ?string, province: ?string}
     */
    public function jsonSerialize(): array
    {
        return $this->detail->jsonSerialize();
    }
}
