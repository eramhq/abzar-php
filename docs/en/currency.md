# Currency

`Eram\Abzar\Money\Currency` formats and converts between Toman and Rial.

```php
use Eram\Abzar\Money\Currency;
use Eram\Abzar\Money\Unit;

Currency::format(1234);                                // ۱،۲۳۴ تومان
Currency::format(12340, Unit::RIAL, persianDigits: false); // 12,340 ریال
Currency::format(1000, withUnit: false);               // ۱،۰۰۰
Currency::convert(1234, Unit::TOMAN, Unit::RIAL);      // 12340
```

## Options

| Arg | Default | Purpose |
|---|---|---|
| `amount` | — | `int`, `float`, or numeric string. Strings are digit-normalized first. |
| `unit` | `TOMAN` | `Unit::TOMAN` or `Unit::RIAL`. |
| `persianDigits` | `true` | Convert output digits to Persian (`۰-۹`). |
| `withUnit` | `true` | Append the unit word (`تومان` / `ریال`). |
| `separator` | `'،'` | Thousands separator. Common alternatives: `'٬'` (ARABIC THOUSANDS SEP), `','`. |

## Conversion

`Currency::convert()` is the Toman ↔ Rial ×10 / ÷10 relationship. Rial → Toman returns an `int` when the input is a multiple of 10, otherwise a `float`.

There is no pluralization in Persian currency words — `۱ تومان` and `۱۰۰ تومان` both use `تومان` — so no locale logic is required.

For arithmetic and comparisons prefer the `Amount` value object (`Eram\Abzar\Money\Amount`), which stores rials internally and exposes `fromRials()` / `fromToman()` factories alongside `add` / `subtract` / comparison helpers.

## Amount

`Eram\Abzar\Money\Amount` is an immutable, non-negative value object. Rials are the canonical internal unit; factories accept either Rials or Toman. Arithmetic and comparison methods return new instances — instances are never mutated.

```php
use Eram\Abzar\Money\Amount;

$subtotal = Amount::fromToman(120_000);
$vat      = $subtotal->percentOf(9);                 // 10,800 rials
$total    = $subtotal->add($vat);                    // 1,210,800 rials
$qty      = $total->times(3);                        // 3,632,400 rials

usort($amounts, fn (Amount $a, Amount $b) => $a->compareTo($b));
```

### Method reference

| Method | Returns | Notes |
|---|---|---|
| `fromRials(int $rials)` | `Amount` | Throws `AMOUNT_NEGATIVE` on negative input. |
| `fromToman(int $toman)` | `Amount` | Throws `AMOUNT_NEGATIVE` on negative, `AMOUNT_OVERFLOW` past `PHP_INT_MAX / 10`. |
| `inRials()` | `int` | |
| `inToman()` | `int` | Truncates when rials are not a multiple of 10. |
| `isZero()` | `bool` | |
| `equals(Amount)` | `bool` | |
| `greaterThan(Amount)` / `lessThan(Amount)` | `bool` | |
| `greaterThanOrEqual(Amount)` / `lessThanOrEqual(Amount)` | `bool` | Threshold checks. |
| `compareTo(Amount)` | `int` | `-1` / `0` / `1`; suitable as a `usort` callback. |
| `add(Amount)` | `Amount` | Throws `AMOUNT_OVERFLOW` when the sum exceeds `PHP_INT_MAX`. |
| `subtract(Amount)` | `Amount` | Throws `AMOUNT_NEGATIVE` when the result would be negative. |
| `times(int $qty)` | `Amount` | Throws `AMOUNT_NEGATIVE` on negative qty, `AMOUNT_OVERFLOW` when the product exceeds `PHP_INT_MAX`. `times(0)` yields zero. |
| `percentOf(int\|float $pct, int $mode = PHP_ROUND_HALF_EVEN)` | `Amount` | Banker's rounding by default. Throws `AMOUNT_NEGATIVE` on negative pct, `AMOUNT_OVERFLOW` on `NAN` / `INF` / overflow. |
| `jsonSerialize()` | `array{rials: int}` | `json_encode($amount)` → `{"rials": …}`. |

All throws surface as `Eram\Abzar\Exception\FormatException`. Catch via the library's base `AbzarException` for a single pipeline-wide handler — see `docs/en/api-stability.md`.
