<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\Exception\ValidationException;
use Eram\Abzar\Data\DataSources;
use Eram\Abzar\Internal\ErrorInput;
use Eram\Abzar\Validation\Details\PhoneNumberDetails;

final class PhoneNumber implements \JsonSerializable, \Stringable
{
    private function __construct(
        private readonly PhoneNumberDetails $detail,
    ) {
    }

    /**
     * A {@code PhoneNumber} VO always represents a number with a resolved
     * operator (mobile) or known area code (landline) — warning-bearing results
     * are rejected here. Use {@see self::validate()} for full-info pass/fail.
     *
     * @throws ValidationException
     */
    public static function from(string $input): self
    {
        $result = self::validate($input);
        if (!$result->isStrictlyValid()) {
            throw ValidationException::fromResult($result);
        }

        /** @var PhoneNumberDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function tryFrom(string $input): ?self
    {
        $result = self::validate($input);
        if (!$result->isStrictlyValid()) {
            return null;
        }

        /** @var PhoneNumberDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function validate(string $input): ValidationResult
    {
        $input = ErrorInput::digits($input, '().');

        if ($input === '') {
            return ValidationResult::invalid(ErrorCode::PHONE_NUMBER_EMPTY);
        }

        if (str_starts_with($input, '+98')) {
            $input = '0' . substr($input, 3);
        } elseif (str_starts_with($input, '0098')) {
            $input = '0' . substr($input, 4);
        } elseif (str_starts_with($input, '98') && strlen($input) === 12) {
            $input = '0' . substr($input, 2);
        } elseif (preg_match('/^9\d{9}$/', $input)) {
            $input = '0' . $input;
        } elseif (strlen($input) === 10 && self::isKnownAreaCode('0' . substr($input, 0, 2))) {
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

        return ValidationResult::invalid(ErrorCode::PHONE_NUMBER_INVALID_FORMAT);
    }

    public static function normalize(string $input): ?string
    {
        $result = self::validate($input);
        if (!$result->isValid()) {
            return null;
        }

        /** @var PhoneNumberDetails $detail */
        $detail = $result->detail();

        return $detail->normalizedLocal;
    }

    /**
     * Generate a valid Iranian mobile number ({@code 09xxxxxxxxx}) for fixtures
     * or tests. Pass {@code $operatorPrefix} to pin the 3-digit operator prefix
     * (e.g. {@code '912'}); otherwise a random known prefix from
     * {@see DataSources::phoneOperators()} is chosen. Named {@code fake} to
     * discourage production use — the number is valid by construction but may
     * not belong to a real subscriber.
     */
    public static function fake(?string $operatorPrefix = null): string
    {
        if ($operatorPrefix === null) {
            $prefixes       = array_keys(DataSources::phoneOperators());
            $operatorPrefix = (string) $prefixes[array_rand($prefixes)];
        }

        if (!preg_match('/^\d{3}$/', $operatorPrefix)) {
            throw new \InvalidArgumentException('operatorPrefix must be exactly 3 digits');
        }

        $body = '0' . $operatorPrefix;
        for ($i = 0; $i < 7; $i++) {
            $body .= (string) random_int(0, 9);
        }

        return $body;
    }

    public function value(): string
    {
        return $this->detail->normalizedLocal;
    }

    public function e164(): string
    {
        return $this->detail->normalizedE164;
    }

    /**
     * Human-readable display form. Mobile local {@code 0912 123 4567}, mobile
     * intl {@code +98 912 123 4567}; landline local {@code 021 8888 7777},
     * landline intl {@code +98 21 8888 7777} (leading {@code 0} of the area
     * code is dropped).
     */
    public function formatted(bool $international = false): string
    {
        $local = $this->detail->normalizedLocal;

        if ($this->isMobile()) {
            $display = substr($local, 0, 4)
                . ' ' . substr($local, 4, 3)
                . ' ' . substr($local, 7, 4);

            return $international ? '+98 ' . substr($display, 1) : $display;
        }

        $area = substr($local, 0, 3);
        $rest = substr($local, 3, 4) . ' ' . substr($local, 7, 4);

        return $international
            ? '+98 ' . substr($area, 1) . ' ' . $rest
            : $area . ' ' . $rest;
    }

    public function type(): PhoneNumberType
    {
        return $this->detail->type;
    }

    public function isMobile(): bool
    {
        return $this->detail->isMobile();
    }

    public function isLandline(): bool
    {
        return $this->detail->isLandline();
    }

    public function operator(): ?string
    {
        return $this->detail->operator;
    }

    public function operatorEnum(): ?Operator
    {
        return $this->detail->operatorEnum();
    }

    public function areaCode(): ?string
    {
        return $this->detail->areaCode;
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

    public function detail(): PhoneNumberDetails
    {
        return $this->detail;
    }

    public function __toString(): string
    {
        return $this->detail->normalizedLocal;
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return $this->detail->jsonSerialize();
    }

    private static function mobileResult(string $normalizedLocal): ValidationResult
    {
        $prefix    = substr($normalizedLocal, 1, 3);
        $operators = DataSources::phoneOperators();
        $operator  = $operators[$prefix] ?? null;

        $detail = PhoneNumberDetails::mobile(
            normalizedLocal: $normalizedLocal,
            normalizedE164:  self::toE164($normalizedLocal),
            operator:        $operator,
        );

        return $operator === null
            ? ValidationResult::validWithWarnings(ErrorCode::PHONE_NUMBER_UNKNOWN_OPERATOR, $detail)
            : ValidationResult::valid($detail);
    }

    private static function landlineResult(string $normalizedLocal): ?ValidationResult
    {
        $areaCode  = substr($normalizedLocal, 0, 3);
        $areaCodes = DataSources::phoneAreaCodes();

        if (!isset($areaCodes[$areaCode])) {
            return null;
        }

        return ValidationResult::valid(PhoneNumberDetails::landline(
            normalizedLocal: $normalizedLocal,
            normalizedE164:  self::toE164($normalizedLocal),
            areaCode:        $areaCode,
            city:            $areaCodes[$areaCode]['city'],
            province:        $areaCodes[$areaCode]['province'],
        ));
    }

    private static function isKnownAreaCode(string $areaCode): bool
    {
        return isset(DataSources::phoneAreaCodes()[$areaCode]);
    }

    private static function toE164(string $normalizedLocal): string
    {
        return '+98' . substr($normalizedLocal, 1);
    }
}
