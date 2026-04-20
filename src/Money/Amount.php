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
        if ($toman < 0) {
            throw FormatException::forInput(ErrorCode::AMOUNT_NEGATIVE, (string) $toman);
        }
        if ($toman > intdiv(PHP_INT_MAX, 10)) {
            throw FormatException::forInput(ErrorCode::AMOUNT_OVERFLOW, (string) $toman);
        }
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

    /**
     * @throws FormatException when the sum would overflow PHP_INT_MAX.
     */
    public function add(self $other): self
    {
        if ($this->rials > PHP_INT_MAX - $other->rials) {
            throw FormatException::forInput(
                ErrorCode::AMOUNT_OVERFLOW,
                $this->rials . '+' . $other->rials,
            );
        }
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

    public function greaterThanOrEqual(self $other): bool
    {
        return $this->rials >= $other->rials;
    }

    public function lessThanOrEqual(self $other): bool
    {
        return $this->rials <= $other->rials;
    }

    /**
     * @return int `-1` / `0` / `1`; suitable as a `usort` callback.
     */
    public function compareTo(self $other): int
    {
        return $this->rials <=> $other->rials;
    }

    /**
     * @throws FormatException when `$qty` is negative or the product would overflow.
     */
    public function times(int $qty): self
    {
        if ($qty < 0) {
            throw FormatException::forInput(ErrorCode::AMOUNT_NEGATIVE, (string) $qty);
        }
        if ($qty !== 0 && $this->rials > intdiv(PHP_INT_MAX, $qty)) {
            throw FormatException::forInput(
                ErrorCode::AMOUNT_OVERFLOW,
                $this->rials . '*' . $qty,
            );
        }
        return new self($this->rials * $qty);
    }

    /**
     * Apply a percentage, rounding to the nearest rial.
     *
     * Past ~2^53 rials the intermediate multiplication loses float precision,
     * so `percentOf(100)` is not byte-exact for amounts above that magnitude.
     * Well outside the realistic IRR range — noted here for completeness.
     *
     * @param int|float $pct  percentage (e.g. `9` for 9% VAT, `0.5` for half a percent).
     * @param 1|2|3|4   $mode one of `PHP_ROUND_HALF_*`; defaults to banker's rounding.
     *
     * @throws FormatException when `$pct` is negative, non-finite, or the result would overflow.
     */
    public function percentOf(int|float $pct, int $mode = PHP_ROUND_HALF_EVEN): self
    {
        if (!is_finite((float) $pct)) {
            throw FormatException::forInput(ErrorCode::AMOUNT_OVERFLOW, is_nan((float) $pct) ? 'NAN' : 'INF');
        }
        if ($pct < 0) {
            throw FormatException::forInput(ErrorCode::AMOUNT_NEGATIVE, (string) $pct);
        }
        $computed = round($this->rials * $pct / 100, 0, $mode);
        // (float) PHP_INT_MAX rounds up to 2^63 (ULP ~2048 at this magnitude),
        // so `>=` is required — any float that equals 2^63 is undefined under (int).
        if (!is_finite($computed) || $computed < 0.0 || $computed >= (float) PHP_INT_MAX) {
            throw FormatException::forInput(
                ErrorCode::AMOUNT_OVERFLOW,
                $this->rials . '*' . $pct . '%',
            );
        }
        return new self((int) $computed);
    }

    /**
     * @return array{rials: int}
     */
    public function jsonSerialize(): array
    {
        return ['rials' => $this->rials];
    }
}
