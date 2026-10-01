# Currency

`Eram\Abzar\Money\Currency` formats and converts between Toman and Rial.

```php
use Eram\Abzar\Money\Amount;
use Eram\Abzar\Money\Currency;
use Eram\Abzar\Money\Unit;

Currency::format(1234);                                // ۱،۲۳۴ تومان
Currency::format(12340, Unit::RIAL, persianDigits: false); // 12،340 ریال
Currency::format(1000, withUnit: false);               // ۱،۰۰۰
Currency::convert(1234, Unit::TOMAN, Unit::RIAL);      // 12340

// An Amount is rendered in the requested unit; sub-toman rials are kept.
Currency::format(Amount::fromRials(12_345));           // '۱،۲۳۴.۵ تومان'
Currency::format(Amount::fromToman(50_000), Unit::RIAL); // '۵۰۰،۰۰۰ ریال'
```

## Options

| Arg | Default | Purpose |
|---|---|---|
| `amount` | — | `int`, `float`, numeric string, or `Amount`. Strings are digit-normalized first and may carry `،` / `٬` / `,` grouping. |
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
$vat      = $subtotal->percentOf(9);                 // 108,000 rials
$total    = $subtotal->add($vat);                    // 1,308,000 rials
$qty      = $total->times(3);                        // 3,924,000 rials

usort($amounts, fn (Amount $a, Amount $b) => $a->compareTo($b));
```

### In words

`toWords()` spells the amount out for cheques, invoices and receipts. Like `Currency::format()`, it defaults to Toman. A rial remainder is spelled out, never truncated:

```php
use Eram\Abzar\Money\Amount;
use Eram\Abzar\Money\Unit;

Amount::fromRials(1_200_000)->toWords();          // 'یکصد و بیست هزار تومان'
Amount::fromRials(1_200_000)->toWords(Unit::RIAL); // 'یک میلیون و دویست هزار ریال'
Amount::fromRials(12_345)->toWords();             // 'یک هزار و دویست و سی و چهار تومان و پنج ریال'
Amount::fromRials(5)->toWords();                  // 'پنج ریال'
Amount::fromRials(0)->toWords();                  // 'صفر تومان'
```

It covers the whole `int` range and never throws. The number words come from `NumberToWords::convert()`.

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
| `toWords(Unit $unit = Unit::TOMAN)` | `string` | Persian words with the unit; a sub-toman remainder is added as `… و N ریال`. |
| `jsonSerialize()` | `array{rials: int}` | `json_encode($amount)` → `{"rials": …}`. |

All throws surface as `Eram\Abzar\Exception\MoneyException` (0.6 and earlier: `FormatException`). Catch via the library's base `AbzarException` for a single pipeline-wide handler — see `docs/en/api-stability.md`.
