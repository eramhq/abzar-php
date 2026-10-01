<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\Data\DataSources;
use Eram\Abzar\Exception\ValidationException;
use Eram\Abzar\Internal\ErrorInput;
use Eram\Abzar\Text\CharNormalizer;
use Eram\Abzar\Validation\Details\PlateNumberDetails;

/**
 * Iranian license plate parser. The canonical shape is
 * {@code NN[letter]NNN-NN}: two digits, a Persian letter, three digits, then
 * a two-digit province code. Whitespace and dashes between groups are tolerated.
 */
final class PlateNumber implements \JsonSerializable, \Stringable
{
    private function __construct(
        private readonly PlateNumberDetails $detail,
    ) {
    }

    /**
     * A {@code PlateNumber} VO always represents a plate with a mapped letter
     * type and known province — warning-bearing results (unknown letter or
     * city code) are rejected here. Use {@see self::validate()} for full-info
     * pass/fail.
     *
     * @throws ValidationException
     */
    public static function from(string $input): self
    {
        $result = self::validate($input);
        if (!$result->isStrictlyValid()) {
            throw ValidationException::fromResult($result);
        }

        /** @var PlateNumberDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function tryFrom(string $input): ?self
    {
        $result = self::validate($input);
        if (!$result->isStrictlyValid()) {
            return null;
        }

        /** @var PlateNumberDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function validate(string $input): ValidationResult
    {
        $input = ErrorInput::digits($input);

        if ($input === '') {
            return ValidationResult::invalid(ErrorCode::PLATE_NUMBER_EMPTY);
        }

        // Letter slot is 1–3 characters (longest key is الف); bound the quantifier
        // so a pathological all-letter input can't be captured whole.
        if (!preg_match('/^(\d{2})(\p{L}{1,3})(\d{3})(\d{2})$/u', $input, $m)) {
            return ValidationResult::invalid(ErrorCode::PLATE_NUMBER_INVALID_FORMAT);
        }

        $letter    = self::normalizeLetter($m[2]);
        $cityCode  = $m[4];
        $typeValue = DataSources::plateLetters()[$letter] ?? null;
        $provinces = DataSources::plateCodes()[$cityCode] ?? [];

        $detail = new PlateNumberDetails(
            twoDigit:   $m[1],
            letter:     $letter,
            threeDigit: $m[3],
            cityCode:   $cityCode,
            type:       $typeValue === null ? PlateType::OTHER : PlateType::from($typeValue),
            province:   $provinces === [] ? null : implode(' - ', $provinces),
            provinces:  $provinces,
        );

        $warnings = [];
        if ($typeValue === null) {
            $warnings[] = ErrorCode::PLATE_NUMBER_UNKNOWN_LETTER;
        }
        if ($provinces === []) {
            $warnings[] = ErrorCode::PLATE_NUMBER_UNKNOWN_CITY_CODE;
        }

        return $warnings === []
            ? ValidationResult::valid($detail)
            : ValidationResult::validWithWarnings($warnings, $detail);
    }

    /**
     * Generate a valid Iranian plate in canonical {@code NN[letter]NNN-NN} form
     * for fixtures or tests. With {@code $type = null}, the letter and city
     * code are picked uniformly at random from the known tables. Passing a
     * {@see PlateType} pins the category to a letter mapped to that type
     * (e.g. {@code PlateType::TAXI} returns a plate with {@code ت}).
     * {@see PlateType::OTHER} is rejected — it represents unknown letters,
     * not a real category. Named {@code fake} to discourage production use.
     */
    public static function fake(?PlateType $type = null): string
    {
        if ($type === PlateType::OTHER) {
            throw new \InvalidArgumentException(
                'PlateType::OTHER cannot be pinned; it represents unknown letters, not a plate category',
            );
        }

        $letterTypes = DataSources::plateLetters();
        if ($type === null) {
            $letters = array_keys($letterTypes);
        } else {
            $letters = array_keys($letterTypes, $type->value, true);
            if ($letters === []) {
                throw new \InvalidArgumentException('No letter mapped to PlateType::' . $type->name);
            }
        }

        $letter     = (string) $letters[array_rand($letters)];
        $cityCodes  = array_keys(DataSources::plateCodes());
        $cityCode   = (string) $cityCodes[array_rand($cityCodes)];
        $twoDigit   = str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT);
        $threeDigit = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);

        return $twoDigit . $letter . $threeDigit . '-' . $cityCode;
    }

    /**
     * Fold Arabic-keyboard ي / ك to the Persian ی / ک used by the letter table.
     */
    private static function normalizeLetter(string $letter): string
    {
        static $normalizer = null;
        $normalizer ??= new CharNormalizer();

        return $normalizer->normalize($letter);
    }

    public function twoDigit(): string
    {
        return $this->detail->twoDigit;
    }

    public function letter(): string
    {
        return $this->detail->letter;
    }

    public function threeDigit(): string
    {
        return $this->detail->threeDigit;
    }

    public function cityCode(): string
    {
        return $this->detail->cityCode;
    }

    public function type(): PlateType
    {
        return $this->detail->type;
    }

    public function province(): ?string
    {
        return $this->detail->province;
    }

    public function detail(): PlateNumberDetails
    {
        return $this->detail;
    }

    public function __toString(): string
    {
        return $this->detail->twoDigit
            . $this->detail->letter
            . $this->detail->threeDigit
            . '-'
            . $this->detail->cityCode;
    }

    /**
     * @return array{
     *     two_digit: string,
     *     letter: string,
     *     three_digit: string,
     *     city_code: string,
     *     type: string,
     *     province: ?string,
     *     provinces: list<string>
     * }
     */
    public function jsonSerialize(): array
    {
        return $this->detail->jsonSerialize();
    }
}
