<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\AbzarValidationException;
use Eram\Abzar\Internal\ErrorInput;
use Eram\Abzar\Validation\Details\PlateNumberDetails;

/**
 * Iranian license plate parser. The canonical shape is
 * {@code NN[letter]NNN-NN}: two digits, a Persian letter, three digits, then
 * a two-digit province code. Whitespace and dashes between groups are tolerated.
 */
final class PlateNumber implements \JsonSerializable, \Stringable
{
    /** @var array<string, PlateType> */
    private const LETTER_TYPES = [
        'الف' => PlateType::PRIVATE,
        'ب'   => PlateType::PRIVATE,
        'پ'   => PlateType::GOVERNMENT_CIV,
        'ت'   => PlateType::TAXI,
        'ث'   => PlateType::POLICE,
        'ج'   => PlateType::PRIVATE,
        'د'   => PlateType::PRIVATE,
        'ز'   => PlateType::DISABLED,
        'ژ'   => PlateType::POLICE,
        'س'   => PlateType::PRIVATE,
        'ش'   => PlateType::MILITARY,
        'ص'   => PlateType::PRIVATE,
        'ط'   => PlateType::RENTAL,
        'ع'   => PlateType::PUBLIC,
        'ف'   => PlateType::RENTAL,
        'ق'   => PlateType::PRIVATE,
        'ک'   => PlateType::AGRICULTURAL,
        'گ'   => PlateType::GOVERNMENT,
        'ل'   => PlateType::PRIVATE,
        'م'   => PlateType::GOVERNMENT,
        'ن'   => PlateType::PRIVATE,
        'و'   => PlateType::PRIVATE,
        'ه'   => PlateType::PRIVATE,
        'ی'   => PlateType::PRIVATE,
        'D'   => PlateType::DIPLOMATIC,
        'S'   => PlateType::DIPLOMATIC,
    ];

    private const CITY_PROVINCE = [
        '10' => 'تهران',              '11' => 'تهران',              '12' => 'تهران',
        '13' => 'تهران',              '14' => 'تهران',              '15' => 'تهران',
        '18' => 'البرز',              '19' => 'البرز',
        '20' => 'مازندران',           '21' => 'مازندران',           '22' => 'مازندران',
        '23' => 'گیلان',              '24' => 'گیلان',
        '25' => 'گلستان',             '26' => 'گلستان',
        '27' => 'قزوین',
        '28' => 'زنجان',
        '29' => 'سمنان',
        '30' => 'خراسان رضوی',        '31' => 'خراسان رضوی',        '32' => 'خراسان رضوی',
        '34' => 'خراسان جنوبی',
        '36' => 'خراسان شمالی',
        '37' => 'سیستان و بلوچستان',
        '42' => 'آذربایجان شرقی',     '43' => 'آذربایجان شرقی',
        '44' => 'اردبیل',
        '45' => 'آذربایجان غربی',
        '47' => 'کردستان',
        '49' => 'کرمانشاه',
        '51' => 'همدان',
        '53' => 'مرکزی',
        '55' => 'ایلام',
        '57' => 'لرستان',
        '59' => 'قم',
        '61' => 'اصفهان',             '63' => 'اصفهان',             '67' => 'اصفهان',
        '71' => 'چهارمحال و بختیاری',
        '73' => 'یزد',
        '74' => 'کهکیلویه و بویراحمد',
        '75' => 'بوشهر',
        '77' => 'خوزستان',
        '83' => 'فارس',               '85' => 'فارس',
        '86' => 'هرمزگان',
        '89' => 'کرمان',
    ];

    private function __construct(
        private readonly PlateNumberDetails $detail,
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

        /** @var PlateNumberDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function tryFrom(string $input): ?self
    {
        $result = self::validate($input);
        if (!$result->isValid()) {
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

        $letter   = $m[2];
        $cityCode = $m[4];
        $type     = self::LETTER_TYPES[$letter] ?? PlateType::OTHER;
        $province = self::CITY_PROVINCE[$cityCode] ?? null;

        $detail = new PlateNumberDetails(
            twoDigit:   $m[1],
            letter:     $letter,
            threeDigit: $m[3],
            cityCode:   $cityCode,
            type:       $type,
            province:   $province,
        );

        $warnings = [];
        if ($type === PlateType::OTHER && !isset(self::LETTER_TYPES[$letter])) {
            $warnings[] = ErrorCode::PLATE_NUMBER_UNKNOWN_LETTER;
        }
        if ($province === null) {
            $warnings[] = ErrorCode::PLATE_NUMBER_UNKNOWN_CITY_CODE;
        }

        return $warnings === []
            ? ValidationResult::valid($detail)
            : ValidationResult::validWithWarnings($warnings, $detail);
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
     *     province: ?string
     * }
     */
    public function jsonSerialize(): array
    {
        return $this->detail->jsonSerialize();
    }
}
