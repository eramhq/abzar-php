# Abzar

**Zero-runtime-dependency Persian (Farsi) utility toolkit for PHP 8.1+.**

Abzar (`ابزار`, "tool") is a pure-PHP library covering the small but opinionated set of utilities every Persian-language application ends up reimplementing: national-ID / IBAN / bank-card / phone validation, number-to-words and time-ago formatting, Persian slug generation, script detection, and digit conversion between Persian, Arabic, and English.

No framework coupling, no runtime extensions beyond stock PHP, no transitive Composer dependencies.

> **Note on messages.** Validation error strings are Persian and byte-identical to the upstream data. They are intentionally not localized in `0.x` — if you are building an English-facing UI, treat them as opaque codes and either map them yourself or wait for stable error-code support in a later release.

## Feature matrix

| Namespace | Class | What it does |
|---|---|---|
| `Validation` | `NationalId` | Iranian national-ID checksum + city / province lookup |
| `Validation` | `LegalId` | 11-digit Iranian legal-entity ID checksum |
| `Validation` | `PhoneNumber` | Iranian mobile number validation + operator detection (`09xx`, `+98`, `0098`, `98`) |
| `Validation` | `CardNumber` | 16-digit bank card Luhn check + bank name from BIN |
| `Validation` | `Iban` | `IR`-prefixed IBAN mod-97 check + bank lookup |
| `Validation` | `ValidationResult` | Shared `{isValid, errors, details}` return type |
| `Format` | `NumberFormatter` | Thousands-separator formatter with digit normalization |
| `Format` | `NumberToWords` | Integer / float to Persian words (`۱۲۳۴` → `یک هزار و دویست و سی و چهار`) |
| `Format` | `OrdinalNumber` | Persian ordinals: `toWord(3)` → `سوم`, `toShort(43)` → `۴۳ام` |
| `Format` | `TimeAgo` | Fuzzy relative time in Persian (`۵ دقیقه پیش`, `حدود ۳ روز پیش`) |
| `Text` | `Script` | `isPersian` / `hasPersian` / `isArabic` / `hasArabic` detectors |
| `Text` | `Slug` | Persian-aware slug (`سلام دنیا` → `سلام-دنیا`) |
| `Text` | `CharNormalizer` | Arabic → Persian char + digit normalization, HTML-aware `normalizeContent()` |
| `Digits` | `DigitConverter` | `toPersian` / `toEnglish` / `toArabic` + HTML-aware `convertContent()` |

## Install

```bash
composer require eram/abzar:^0.1@beta
```

Requires PHP 8.1+. No runtime extensions beyond `mbstring`.

## Quick examples

### Validation

```php
use Eram\Abzar\Validation\NationalId;
use Eram\Abzar\Validation\Iban;
use Eram\Abzar\Validation\CardNumber;
use Eram\Abzar\Validation\PhoneNumber;

$r = NationalId::validate('0013542419');
$r->isValid();              // true
$r->details();              // ['city_code' => '001', 'city' => 'تهران مرکزی', 'province' => 'تهران']

Iban::validate('IR820540102680020817909002')->details()['bank'];     // 'بانک پارسیان'
CardNumber::validate('6037 9912 3456 7893')->details()['bank'];       // 'بانک ملی ایران'
PhoneNumber::validate('+989121234567')->details()['operator'];        // 'همراه اول'
PhoneNumber::normalize('+989121234567');                              // '09121234567'
```

### Formatting

```php
use Eram\Abzar\Format\NumberFormatter;
use Eram\Abzar\Format\NumberToWords;
use Eram\Abzar\Format\OrdinalNumber;
use Eram\Abzar\Format\TimeAgo;

NumberFormatter::withSeparators(1234567);         // '1,234,567'
NumberFormatter::withSeparators('۱۲۳۴۵۶۷');       // '1,234,567'

NumberToWords::convert(1984);                     // 'یک هزار و نهصد و هشتاد و چهار'
NumberToWords::convert(3.25);                     // 'سه ممیز بیست و پنج'

OrdinalNumber::toWord(43);                        // 'چهل و سوم'
OrdinalNumber::toShort(43);                       // '۴۳ام'

TimeAgo::format(time() - 300);                    // '۵ دقیقه پیش'
```

### Text

```php
use Eram\Abzar\Text\Script;
use Eram\Abzar\Text\Slug;
use Eram\Abzar\Text\CharNormalizer;

Script::isPersian('سلام دنیا');                    // true
Script::hasArabic('متن فارسی با كلمة عربي');       // true

Slug::generate('سلام دنیا');                       // 'سلام-دنیا'
Slug::generate('محصول ۱۲۳');                       // 'محصول-123'

$n = new CharNormalizer();
$n->normalize('كتابي ٠١٢');                        // 'کتابی ۰۱۲'
$n->normalizeContent('<p>كتابي</p>');              // '<p>کتابی</p>' (HTML-aware)
$n->normalizeForSearch('۱۲۳ كتاب');                // '123 کتاب' (digits → English)
```

### Digits

```php
use Eram\Abzar\Digits\DigitConverter;

DigitConverter::toPersian('Version 1.2');          // 'Version ۱.۲'
DigitConverter::toEnglish('نسخه ۱.۲');             // 'نسخه 1.2'
DigitConverter::toArabic('1234');                  // '١٢٣٤'

// HTML-aware: leaves tags, scripts, styles, and attributes alone
DigitConverter::convertContent('<a href="page-5">Item 5</a>');
// '<a href="page-5">Item ۵</a>'
```

## Related packages

Abzar deliberately stays narrow. Two companion packages cover adjacent ground:

- [`eramhq/daynum`](https://github.com/eramhq/daynum) — jalali / shamsi calendar utilities. Abzar does **not** ship calendar logic; install daynum for anything date-related.
- [`eramhq/persian-kit`](https://github.com/eramhq/persian-kit) — WordPress plugin that wires abzar into WP hooks (`the_content`, `sanitize_title`, `pre_get_posts`), adds admin tools for one-shot database normalization, and exposes shortcodes / blocks.

See [`docs/en/related.md`](docs/en/related.md) for a longer comparison.

## Versus other Persian PHP libraries

|  | abzar | [persian-tools (JS)](https://github.com/persian-tools/persian-tools) | [nikapps/iran-validator](https://github.com/nikapps/iran-validator) |
|---|---|---|---|
| Language | PHP 8.1+ | JS/TS | PHP 7.4+ |
| Zero runtime deps | Yes | — | Yes |
| Typed result object (`isValid`/`errors`/`details`) | Yes | Partial | No (bool only) |
| `JsonSerializable` result | Yes | n/a | No |
| Structured error codes (roadmap) | Yes (`0.3`) | No | No |
| Bank card / IBAN / phone / national-ID / legal-ID | Yes | Yes | Subset |
| Number-to-words / time-ago / ordinals | Yes | Yes | No |
| Slug / char normalize / digit convert | Yes | Partial | No |
| WordPress integration | Via `eramhq/persian-kit` | No | No |

## Framework bridges

Abzar stays framework-agnostic. Integration recipes for Laravel FormRequest, Symfony Validator, Symfony Console, and WordPress live under [`docs/en/recipes/`](docs/en/recipes). Each is a few dozen lines — paste into your project and tweak.

## Stability

Abzar is in `0.x`. Breaking changes may happen before `1.0`; pin with `^0.1@beta` until the API stabilizes. The [API stability policy](docs/en/api-stability.md) spells out which parts of the surface are protected.

## License

MIT. See [LICENSE](LICENSE). Parts of the validation data tables are derived from the MIT-licensed [persian-tools](https://github.com/persian-tools/persian-tools) project.
