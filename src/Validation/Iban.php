<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\AbzarValidationException;
use Eram\Abzar\Data\DataSources;
use Eram\Abzar\Digits\DigitConverter;
use Eram\Abzar\Validation\Details\IbanDetails;

final class Iban implements \JsonSerializable, \Stringable
{
    private function __construct(
        private readonly IbanDetails $detail,
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

        /** @var IbanDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function tryFrom(string $input): ?self
    {
        $result = self::validate($input);
        if (!$result->isValid()) {
            return null;
        }

        /** @var IbanDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function validate(string $input): ValidationResult
    {
        $input = DigitConverter::toEnglish(trim($input));
        $input = strtoupper((string) preg_replace('/\s/', '', $input));

        if ($input === '') {
            return ValidationResult::invalid(ErrorCode::IBAN_EMPTY);
        }

        if (preg_match('/^\d{24}$/', $input)) {
            $input = 'IR' . $input;
        }

        if (!preg_match('/^IR\d{24}$/', $input)) {
            if (preg_match('/^[A-Z]{2}/', $input) && !str_starts_with($input, 'IR')) {
                return ValidationResult::invalid(ErrorCode::IBAN_MISSING_PREFIX);
            }
            return ValidationResult::invalid(ErrorCode::IBAN_WRONG_LENGTH);
        }

        if (!self::mod97($input)) {
            return ValidationResult::invalid(ErrorCode::IBAN_INVALID_CHECKSUM);
        }

        $bankCode = substr($input, 4, 3);
        $bank     = DataSources::ibanBanks()[$bankCode] ?? null;

        return ValidationResult::valid(new IbanDetails(
            value:    $input,
            bankCode: $bankCode,
            bank:     $bank,
        ));
    }

    public function value(): string
    {
        return $this->detail->value;
    }

    public function bankCode(): string
    {
        return $this->detail->bankCode;
    }

    public function bank(): ?string
    {
        return $this->detail->bank;
    }

    public function bankEnum(): ?Bank
    {
        return $this->detail->bankEnum();
    }

    public function detail(): IbanDetails
    {
        return $this->detail;
    }

    public function __toString(): string
    {
        return $this->detail->value;
    }

    /**
     * @return array{value: string, bank_code: string, bank: ?string}
     */
    public function jsonSerialize(): array
    {
        return $this->detail->jsonSerialize();
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
