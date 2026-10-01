<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\Data\DataSources;
use Eram\Abzar\Exception\ValidationException;
use Eram\Abzar\Internal\ErrorInput;
use Eram\Abzar\Internal\Extractor;
use Eram\Abzar\Validation\Details\PhoneNumberDetails;

final class PhoneNumber implements \JsonSerializable, \Stringable
{
    private function __construct(
        private readonly PhoneNumberDetails $detail,
    ) {
    }

    /**
     * Accepts every valid number, including warning-bearing results: an
     * unknown mobile prefix yields a null operator, an unknown landline area
     * code a null city / province. Check
     * {@code PhoneNumber::validate($x)->isStrictlyValid()} first when only
     * catalogued operators / area codes are acceptable.
     *
     * @throws ValidationException
     */
    public static function from(string $input): self
    {
        $result = self::validate($input);
        if (!$result->isValid()) {
            throw ValidationException::fromResult($result);
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

        // Area codes are 0[1-8]x; "00…" is an international prefix, not a landline.
        if (preg_match('/^0[1-8]\d{9}$/', $input)) {
            return self::landlineResult($input);
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
     * Generate a valid Iranian phone number for fixtures or tests — a mobile
     * ({@code 09xxxxxxxxx}) by default, or a landline ({@code 0AAxxxxxxxx})
     * with {@code $type = PhoneNumberType::LANDLINE}. Pin the 3-digit operator
     * prefix (e.g. {@code '912'}) for mobiles or the area code (e.g.
     * {@code '021'}) for landlines; otherwise a random catalogued one is used.
     * Named {@code fake} to discourage production use — the number is valid by
     * construction but may belong to a real subscriber.
     *
     * @throws ValidationException when a pin is malformed or doesn't match $type.
     */
    public static function fake(
        ?string $operatorPrefix = null,
        PhoneNumberType $type = PhoneNumberType::MOBILE,
        ?string $areaCode = null,
    ): string {
        if ($type === PhoneNumberType::LANDLINE) {
            if ($operatorPrefix !== null) {
                throw ValidationException::forFakeArgument('operatorPrefix only applies to mobile numbers; pin areaCode instead');
            }
            if ($areaCode === null) {
                $codes    = array_keys(DataSources::phoneAreaCodes());
                $areaCode = (string) $codes[array_rand($codes)];
            }
            if (!preg_match('/^0[1-8]\d$/', $areaCode)) {
                throw ValidationException::forFakeArgument('areaCode must be 3 digits shaped 0[1-8]x');
            }

            return $areaCode . random_int(2, 8) . self::randomDigits(7);
        }

        if ($areaCode !== null) {
            throw ValidationException::forFakeArgument('areaCode only applies to landlines; pass PhoneNumberType::LANDLINE');
        }
        if ($operatorPrefix === null) {
            $prefixes       = array_keys(DataSources::phoneOperators());
            $operatorPrefix = (string) $prefixes[array_rand($prefixes)];
        }
        if (!preg_match('/^\d{3}$/', $operatorPrefix)) {
            throw ValidationException::forFakeArgument('operatorPrefix must be exactly 3 digits');
        }

        return '0' . $operatorPrefix . self::randomDigits(7);
    }

    /**
     * Scan free text for Iranian phone numbers written with a leading {@code 0},
     * {@code +98} or {@code 0098} (optionally grouped with spaces or dashes)
     * and return each valid one, left to right. Bare 10-digit runs are skipped
     * as too ambiguous.
     *
     * @return list<self>
     */
    public static function extractAll(string $text): array
    {
        return Extractor::all(
            $text,
            '/(?<![\d+])(?:(?:\+|00)98[\s-]?|0)(?:9\d{2}|[1-8]\d)[\s-]?\d{3,4}[\s-]?\d{4}(?!\d)/u',
            self::tryFrom(...),
        );
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

    /**
     * Display form with the subscriber's middle digits hidden: mobile
     * {@code 0912 *** 4567}, landline {@code 021 **** 7777}.
     */
    public function masked(): string
    {
        $local = $this->detail->normalizedLocal;

        return $this->isMobile()
            ? substr($local, 0, 4) . ' *** ' . substr($local, 7, 4)
            : substr($local, 0, 3) . ' **** ' . substr($local, 7, 4);
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

    private static function landlineResult(string $normalizedLocal): ValidationResult
    {
        $areaCode = substr($normalizedLocal, 0, 3);
        $area     = DataSources::phoneAreaCodes()[$areaCode] ?? null;

        $detail = PhoneNumberDetails::landline(
            normalizedLocal: $normalizedLocal,
            normalizedE164:  self::toE164($normalizedLocal),
            areaCode:        $areaCode,
            city:            $area['city'] ?? null,
            province:        $area['province'] ?? null,
        );

        return $area === null
            ? ValidationResult::validWithWarnings(ErrorCode::PHONE_NUMBER_UNKNOWN_AREA_CODE, $detail)
            : ValidationResult::valid($detail);
    }

    private static function randomDigits(int $count): string
    {
        $out = '';
        for ($i = 0; $i < $count; $i++) {
            $out .= (string) random_int(0, 9);
        }

        return $out;
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
