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
