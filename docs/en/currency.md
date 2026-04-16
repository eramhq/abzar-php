# Currency

`Eram\Abzar\Format\Currency` formats and converts between Toman and Rial.

```php
use Eram\Abzar\Format\Currency;
use Eram\Abzar\Format\CurrencyUnit;

Currency::format(1234);                                            // ۱،۲۳۴ تومان
Currency::format(12340, CurrencyUnit::RIAL, persianDigits: false); // 12,340 ریال
Currency::format(1000, withUnit: false);                           // ۱،۰۰۰
Currency::convert(1234, CurrencyUnit::TOMAN, CurrencyUnit::RIAL);  // 12340
```

## Options

| Arg | Default | Purpose |
|---|---|---|
| `amount` | — | `int`, `float`, or numeric string. Strings are digit-normalized first. |
| `unit` | `TOMAN` | `CurrencyUnit::TOMAN` or `CurrencyUnit::RIAL`. |
| `persianDigits` | `true` | Convert output digits to Persian (`۰-۹`). |
| `withUnit` | `true` | Append the unit word (`تومان` / `ریال`). |
| `separator` | `'،'` | Thousands separator. Common alternatives: `'٬'` (ARABIC THOUSANDS SEP), `','`. |

## Conversion

`Currency::convert()` is the Toman ↔ Rial ×10 / ÷10 relationship. Rial → Toman returns an `int` when the input is a multiple of 10, otherwise a `float`.

There is no pluralization in Persian currency words — `۱ تومان` and `۱۰۰ تومان` both use `تومان` — so no locale logic is required.
