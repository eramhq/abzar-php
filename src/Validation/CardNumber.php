<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\Data\DataSources;
use Eram\Abzar\Exception\ValidationException;
use Eram\Abzar\Internal\ErrorInput;
use Eram\Abzar\Validation\Details\CardNumberDetails;

final class CardNumber implements \JsonSerializable, \Stringable
{
    private function __construct(
        private readonly CardNumberDetails $detail,
    ) {
    }

    /**
     * A {@code CardNumber} VO always represents a fully validated Iranian card
     * with a resolved bank — warning-bearing results (e.g. unknown BIN) are
     * rejected here. Use {@see self::validate()} for full-info pass/fail.
     *
     * @throws ValidationException
     */
    public static function from(string $input): self
    {
        $result = self::validate($input);
        if (!$result->isStrictlyValid()) {
            throw ValidationException::fromResult($result);
        }

        /** @var CardNumberDetails $detail */
        $detail = $result->detail();

        return new self($detail);
    }

    public static function tryFrom(string $input): ?self
    {
        $result = self::validate($input);
        if (!$result->isStrictlyValid()) {
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

        // All-zeros is a degenerate Luhn pass; no legitimate card has that shape.
        if (preg_match('/^(\d)\1{15}$/', $input)) {
            return ValidationResult::invalid(ErrorCode::CARD_NUMBER_INVALID_CHECKSUM);
        }

        if (!self::luhn($input)) {
            return ValidationResult::invalid(ErrorCode::CARD_NUMBER_INVALID_CHECKSUM);
        }

        $bin    = substr($input, 0, 6);
        $banks  = DataSources::cardBanks();
        $bank   = $banks[$bin] ?? null;
        $detail = new CardNumberDetails(value: $input, bin: $bin, bank: $bank);

        return $bank === null
            ? ValidationResult::validWithWarnings(ErrorCode::CARD_NUMBER_UNKNOWN_BIN, $detail)
            : ValidationResult::valid($detail);
    }

    /**
     * Generate a Luhn-valid 16-digit card for fixtures or tests. Pass {@code $bin}
     * to pin the 6-digit BIN; otherwise a random known-bank BIN is chosen.
     * Named {@code fake} to discourage production use — these pass Luhn but
     * are not real cards.
     */
    public static function fake(?string $bin = null): string
    {
        if ($bin === null) {
            $bins = array_keys(DataSources::cardBanks());
            $bin  = (string) $bins[array_rand($bins)];
        }

        if (!preg_match('/^\d{6}$/', $bin)) {
            throw new \InvalidArgumentException('bin must be exactly 6 digits');
        }

        $body = $bin;
        for ($i = 0; $i < 9; $i++) {
            $body .= (string) random_int(0, 9);
        }

        // Appending '0' puts the check digit in the i=0 (rightmost, unchanged)
        // slot, so the check value is whatever makes the total sum 0 mod 10.
        $check = (10 - self::luhnSum($body . '0') % 10) % 10;

        return $body . $check;
    }

    /**
     * Scan free text for 16-digit runs (optionally spaced / dashed) and return
     * each that parses as a valid card. Run order follows left-to-right. Only
     * cards with a bundled BIN are returned — unknown-BIN Luhn-valid candidates
     * are dropped (same guarantee as {@see self::from()}).
     *
     * @return list<self>
     */
    public static function extractAll(string $text): array
    {
        $english = \Eram\Abzar\Digits\DigitConverter::toEnglish($text);
        // Match 16 digits allowing single spaces or dashes between groups.
        preg_match_all('/(?<!\d)(?:\d[\s-]?){15}\d(?!\d)/', $english, $matches);

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

    /**
     * PCI-style masked form: first 6 + last 4 shown, middle 6 replaced with
     * {@code *}, grouped in 4s (e.g. {@code 6037 99** **** 7893}).
     */
    public function masked(): string
    {
        $v      = $this->detail->value;
        $masked = substr($v, 0, 6) . '******' . substr($v, 12, 4);

        return implode(' ', str_split($masked, 4));
    }

    /**
     * 4-4-4-4 grouped display (e.g. {@code 6037 9912 3456 7893}).
     */
    public function formatted(): string
    {
        return implode(' ', str_split($this->detail->value, 4));
    }

    public function bin(): string
    {
        return $this->detail->bin;
    }

    public function bank(): ?string
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
     * @return array{value: string, bin: string, bank: ?string}
     */
    public function jsonSerialize(): array
    {
        return $this->detail->jsonSerialize();
    }

    private static function luhn(string $number): bool
    {
        return self::luhnSum($number) % 10 === 0;
    }

    private static function luhnSum(string $number): int
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

        return $sum;
    }
}
