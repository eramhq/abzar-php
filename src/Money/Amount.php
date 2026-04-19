<?php

declare(strict_types=1);

namespace Eram\Abzar\Money;

use Eram\Abzar\Exception\FormatException;
use Eram\Abzar\Validation\ErrorCode;

/**
 * Immutable value object representing a monetary amount in Iranian currency.
 *
 * Stored internally as Rials (IRR) to eliminate the Rial/Toman ×10 confusion
 * that plagues Iranian payment and billing code. Construct via
 * {@see self::fromRials()} or {@see self::fromToman()}; arithmetic returns
 * new instances.
 *
 * For display formatting (Persian digits, thousand separators, unit suffix)
 * pair with {@see \Eram\Abzar\Money\Currency}. Persian / Arabic digit strings
 * are a Validation-layer concern — normalize upstream via
 * {@see \Eram\Abzar\Digits\DigitConverter} before constructing.
 */
final class Amount implements \JsonSerializable
{
    private function __construct(
        private readonly int $rials,
    ) {
        if ($rials < 0) {
            throw FormatException::forInput(ErrorCode::AMOUNT_NEGATIVE, (string) $rials);
        }
    }

    /**
     * @throws FormatException
     */
    public static function fromRials(int $rials): self
    {
        return new self($rials);
    }

    /**
     * @throws FormatException
     */
    public static function fromToman(int $toman): self
    {
        return new self($toman * 10);
    }

    public function inRials(): int
    {
        return $this->rials;
    }

    public function inToman(): int
    {
        return intdiv($this->rials, 10);
    }

    public function equals(self $other): bool
    {
        return $this->rials === $other->rials;
    }

    public function isZero(): bool
    {
        return $this->rials === 0;
    }

    public function add(self $other): self
    {
        return new self($this->rials + $other->rials);
    }

    /**
     * @throws FormatException when the result would be negative.
     */
    public function subtract(self $other): self
    {
        return new self($this->rials - $other->rials);
    }

    public function greaterThan(self $other): bool
    {
        return $this->rials > $other->rials;
    }

    public function lessThan(self $other): bool
    {
        return $this->rials < $other->rials;
    }

    /**
     * @return array{rials: int}
     */
    public function jsonSerialize(): array
    {
        return ['rials' => $this->rials];
    }
}
