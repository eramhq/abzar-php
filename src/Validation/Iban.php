<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\Data\DataSources;
use Eram\Abzar\Exception\ValidationException;
use Eram\Abzar\Internal\ErrorInput;
use Eram\Abzar\Internal\Extractor;
use Eram\Abzar\Validation\Details\IbanDetails;

/**
 * Iranian IBAN (شبا) — {@code IR} + 2 check digits + 3-digit bank code + 19
 * account digits, ISO 13616 mod-97. A checksum-valid IBAN whose bank code
 * isn't in the bundled table is valid with an {@see ErrorCode::IBAN_UNKNOWN_BANK}
 * warning and a null bank.
 */
final class Iban implements \JsonSerializable, \Stringable
{
    private function __construct(
        private readonly IbanDetails $detail,
    ) {
    }

    /**
     * @throws ValidationException
     */
    public static function from(string $input): self
    {
        $result = self::validate($input);
        if (!$result->isValid()) {
            throw ValidationException::fromResult($result);
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
        $input = strtoupper(ErrorInput::digits($input));

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
        $detail   = new IbanDetails(value: $input, bankCode: $bankCode, bank: $bank);

        return $bank === null
            ? ValidationResult::validWithWarnings(ErrorCode::IBAN_UNKNOWN_BANK, $detail)
            : ValidationResult::valid($detail);
    }

    /**
     * Generate a valid Iranian IBAN ({@code IR} + 24 digits) for fixtures or
     * tests. Pass {@code $bankCode} to pin the 3-digit bank code; otherwise a
     * random known code from {@see DataSources::ibanBanks()} is chosen. Check
     * digits are computed via the ISO 13616 mod-97 algorithm so the result
     * round-trips through {@see self::validate()}. Named {@code fake} to
     * discourage production use — the IBAN is valid by construction but may
     * not correspond to a real account.
     */
    public static function fake(?string $bankCode = null): string
    {
        if ($bankCode === null) {
            $codes     = array_keys(DataSources::ibanBanks());
            $bankCode  = (string) $codes[array_rand($codes)];
        }

        if (!preg_match('/^\d{3}$/', $bankCode)) {
            throw ValidationException::forFakeArgument('bankCode must be exactly 3 digits');
        }

        $account = '';
        for ($i = 0; $i < 19; $i++) {
            $account .= (string) random_int(0, 9);
        }

        $remainder = self::mod97Remainder('IR00' . $bankCode . $account);
        $check     = str_pad((string) (98 - $remainder), 2, '0', STR_PAD_LEFT);

        return 'IR' . $check . $bankCode . $account;
    }

    /**
     * Scan free text for {@code IR} + 24 digits (case-insensitive, optionally
     * grouped with spaces or dashes) and return each valid IBAN, left to right.
     *
     * @return list<self>
     */
    public static function extractAll(string $text): array
    {
        return Extractor::all($text, '/(?<![A-Za-z0-9])IR(?:[\s-]?\d){24}(?!\d)/iu', self::tryFrom(...));
    }

    public function value(): string
    {
        return $this->detail->value;
    }

    /**
     * Masked display that keeps {@code IR}, the check digits, the bank code
     * and the last 4 digits, grouped like {@see self::formatted()}
     * (e.g. {@code IR82 054* **** **** **** **90 02}).
     */
    public function masked(): string
    {
        $v = $this->detail->value;

        return implode(' ', str_split(substr($v, 0, 7) . str_repeat('*', 15) . substr($v, 22), 4));
    }

    /**
     * 4-char grouped display (e.g. {@code IR82 0540 1026 8002 0817 9090 02}).
     * Final group is the 2-char tail.
     */
    public function formatted(): string
    {
        return implode(' ', str_split($this->detail->value, 4));
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
        return self::mod97Remainder($iban) === 1;
    }

    /**
     * ISO 13616 rearrangement + digit-by-digit mod 97. Check digits pass when
     * the result is {@code 1}; to compute check digits for a new IBAN, run the
     * placeholder ({@code IR00…}) through this and subtract from {@code 98}.
     */
    private static function mod97Remainder(string $iban): int
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

        return (int) $remainder;
    }
}
