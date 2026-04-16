<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\AbzarValidationException;
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
     * @throws AbzarValidationException
     */
    public static function from(string $input): self
    {
        $result = self::validate($input);
        if (!$result->isValid()) {
            throw AbzarValidationException::fromResult($result);
        }

        /** @var PhoneNumberDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function tryFrom(string $input): ?self
    {
        $result = self::validate($input);
        if (!$result->isValid()) {
            return null;
        }

        /** @var PhoneNumberDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function validate(string $input): ValidationResult
    {
        $input = ErrorInput::digits($input, '()');

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
        }

        if (preg_match('/^09\d{9}$/', $input)) {
            $mobile = self::mobileResult($input);
            if ($mobile !== null) {
                return $mobile;
            }
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

    public function value(): string
    {
        return $this->detail->normalizedLocal;
    }

    public function e164(): string
    {
        return $this->detail->normalizedE164;
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

    private static function mobileResult(string $normalizedLocal): ?ValidationResult
    {
        $prefix    = substr($normalizedLocal, 1, 3);
        $operators = DataSources::phoneOperators();

        if (!isset($operators[$prefix])) {
            return null;
        }

        return ValidationResult::valid(PhoneNumberDetails::mobile(
            normalizedLocal: $normalizedLocal,
            normalizedE164:  self::toE164($normalizedLocal),
            operator:        $operators[$prefix],
        ));
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

    private static function toE164(string $normalizedLocal): string
    {
        return '+98' . substr($normalizedLocal, 1);
    }
}
