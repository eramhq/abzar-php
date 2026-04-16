<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\AbzarValidationException;
use Eram\Abzar\Data\DataSources;
use Eram\Abzar\Internal\ErrorInput;
use Eram\Abzar\Validation\Details\CardNumberDetails;

final class CardNumber implements \JsonSerializable, \Stringable
{
    private function __construct(
        private readonly CardNumberDetails $detail,
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

        /** @var CardNumberDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function tryFrom(string $input): ?self
    {
        $result = self::validate($input);
        if (!$result->isValid()) {
            return null;
        }

        /** @var CardNumberDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function validate(string $input): ValidationResult
    {
        $input = ErrorInput::digits($input);

        if ($input === '') {
            return ValidationResult::invalid(ErrorCode::CARD_NUMBER_EMPTY);
        }

        if (!preg_match('/^\d{16}$/', $input)) {
            return ValidationResult::invalid(ErrorCode::CARD_NUMBER_WRONG_LENGTH);
        }

        $bin   = substr($input, 0, 6);
        $banks = DataSources::cardBanks();
        if (!isset($banks[$bin])) {
            return ValidationResult::invalid(ErrorCode::CARD_NUMBER_INVALID_CHECKSUM);
        }

        if (!self::luhn($input)) {
            return ValidationResult::invalid(ErrorCode::CARD_NUMBER_INVALID_CHECKSUM);
        }

        return ValidationResult::valid(new CardNumberDetails(
            value: $input,
            bin:   $bin,
            bank:  $banks[$bin],
        ));
    }

    public function value(): string
    {
        return $this->detail->value;
    }

    public function bin(): string
    {
        return $this->detail->bin;
    }

    public function bank(): string
    {
        return $this->detail->bank;
    }

    public function bankEnum(): ?Bank
    {
        return $this->detail->bankEnum();
    }

    public function detail(): CardNumberDetails
    {
        return $this->detail;
    }

    public function __toString(): string
    {
        return $this->detail->value;
    }

    /**
     * @return array{value: string, bin: string, bank: string}
     */
    public function jsonSerialize(): array
    {
        return $this->detail->jsonSerialize();
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
