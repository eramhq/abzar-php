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

## WordPress

Using WordPress? See [`eramhq/persian-kit`](https://github.com/eramhq/persian-kit) — a plugin that wires Abzar into the WordPress hooks you'd expect (`the_content`, `sanitize_title`, `pre_get_posts`, etc.) and adds admin tools for one-shot database normalization.

## Stability

Abzar is in `0.x`. Breaking changes may happen before `1.0`; pin with `^0.1@beta` until the API stabilizes.

## License

MIT. See [LICENSE](LICENSE). Parts of the validation data tables are derived from the MIT-licensed [persian-tools](https://github.com/persian-tools/persian-tools) project.
