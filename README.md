# Abzar

**Zero-runtime-dependency Persian (Farsi) utility toolkit for PHP 8.1+.**

Abzar (`ابزار`, "tool") is a pure-PHP library covering the small but opinionated set of utilities every Persian-language application ends up reimplementing: national-ID / IBAN / bank-card / phone validation, number-to-words and time-ago formatting, Persian slug generation, script detection, and digit conversion between Persian, Arabic, and English.

No framework coupling, no runtime extensions beyond stock PHP, no transitive Composer dependencies.

> **Messages and error codes.** Persian error messages are byte-identical to the upstream data. For language-neutral error handling, every validator failure also emits a machine-readable `ErrorCode`:
>
> ```php
> use Eram\Abzar\Validation\{CardNumber, ErrorCode};
>
> $r = CardNumber::validate('');
> $r->errorCodes();                                  // [ErrorCode::CARD_NUMBER_EMPTY]
> in_array(ErrorCode::CARD_NUMBER_EMPTY, $r->errorCodes(), true); // true
> ```
>
> Error-code values are stable API surface as of `0.3` — renaming a case is a breaking change.

> **Exception hierarchy.** Every thrown exception extends `Eram\Abzar\Exception\AbzarException` (abstract; carries `errorCode(): ErrorCode`). Three concrete subclasses: `ValidationException` (thrown by `::from()`), `FormatException` (thrown by formatters), and `EnvironmentException` (thrown when an optional extension like `ext-intl` is missing at runtime). Catch the base to handle every library failure uniformly.

## Feature matrix

| Namespace | Class | What it does |
|---|---|---|
| `Validation` | `NationalId` | Iranian national-ID checksum + city / province lookup |
| `Validation` | `LegalId` | 11-digit Iranian legal-entity ID checksum |
| `Validation` | `PhoneNumber` | Iranian mobile + landline number validation, operator / area-code detection (`09xx`, `+98`, `0098`, `98`) |
| `Validation` | `CardNumber` | 16-digit bank card Luhn check + bank name from BIN |
| `Validation` | `Iban` | `IR`-prefixed IBAN mod-97 check + bank lookup |
| `Validation` | `PostalCode` | 10-digit Iranian postal code validator |
| `Validation` | `BillId` | `شناسه قبض` / `شناسه پرداخت` mod-11 pair validator with bill-type decoding |
| `Validation` | `PlateNumber` | Iranian license plate (`NN[letter]NNN-NN`) parser with letter-derived type + province lookup |
| `Validation` | `ErrorCode` | Stable `DOMAIN.REASON` codes emitted by every validator + format exception |
| `Validation` | `Bank` / `Operator` / `Province` / `PlateType` | Typed enums with `fromPersian()` lookup and Arabic-char-tolerant matching |
| `Validation` | `ValidationResult` | Shared `{isValid, errors, errorCodes, warnings, detail}` return type (implements `JsonSerializable` / `Stringable`) |
| `Validation\Details` | `ValidationDetail` | Marker interface for the per-validator readonly DTOs returned from `ValidationResult::detail()` |
| `Format` | `NumberFormatter` | Thousands-separator formatter with digit normalization |
| `Format` | `NumberToWords` | Integer / float to Persian words (`۱۲۳۴` → `یک هزار و دویست و سی و چهار`) |
| `Format` | `WordsToNumber` | Parse Persian number words back to `int` / `float` |
| `Format` | `OrdinalNumber` | Persian ordinals: `toWord(3)` → `سوم`, `toShort(43)` → `۴۳ام` |
| `Format` | `TimeAgo` | Fuzzy relative time in Persian (`۵ دقیقه پیش`, `حدود ۳ روز پیش`) |
| `Money` | `Amount` | Immutable Iranian-currency value object; stores rials internally, factories / accessors for both units |
| `Money` | `Currency` / `Unit` | Toman / Rial formatter and ×10 / ÷10 converter |
| `Text` | `Script` | `isPersian` / `hasPersian` / `isArabic` / `hasArabic` detectors |
| `Text` | `Slug` | Persian-aware slug (`سلام دنیا` → `سلام-دنیا`) |
| `Text` | `CharNormalizer` | Arabic → Persian char + digit normalization, HTML-aware `normalizeContent()`, opt-in hamza / tashkeel / kashida / NFC flags |
| `Text` | `KeyboardFixer` | Swap between English QWERTY and Persian keyboard layouts, with a `detect()` heuristic |
| `Text` | `PersianCollator` | `ext-intl`-backed `fa_IR` collator with `sort` / `sortBy` helpers |
| `Text` | `HalfSpaceFixer` | Best-effort zero-width-non-joiner placement for compound-word affixes (`می‌روم`, `خانه‌ها`, `بزرگ‌ترین`) |
| `Digits` | `DigitConverter` | `toPersian` / `toEnglish` / `toArabic` + HTML-aware `convertContent()` |

## Install

```bash
composer require eram/abzar:^0.5@beta
```

Requires PHP 8.1+. No runtime extensions beyond `mbstring`.

## Quick examples

### Validation

Three entry points per validator (same pattern as `BackedEnum`), ordered by how most apps use them:

```php
use Eram\Abzar\Validation\{NationalId, Iban, CardNumber, PhoneNumber};

// 1. ValidationResult for plain pass/fail checks with full error detail.
$r = CardNumber::validate('6037 9912 3456 7893');
$r->isValid();             // true
$r->detail()->bank;        // 'بانک ملی ایران' (CardNumberDetails)
$r->errorCodes();          // [] (empty on success)

// 2. Null-returning variant.
$phone = PhoneNumber::tryFrom('+989121234567');
$phone?->e164();           // '+989121234567'
$phone?->operatorEnum();   // Operator::MCI
$phone?->isMobile();       // true

// 3. Value object on success — throws ValidationException on failure.
$ni = NationalId::from('0013542419');
$ni->value();              // '0013542419'
$ni->city();               // 'تهران مرکزی'
$ni->province();           // 'تهران'
$ni->cityCode();           // '001'

Iban::from('IR820540102680020817909002')->bankEnum();  // Bank::PARSIAN
PhoneNumber::normalize('+989121234567');               // '09121234567'
```

> **`isValid()` vs `isStrictlyValid()`.** `validate()` can return `true` with a non-fatal warning when the input parses cleanly but an optional lookup fails (unknown card BIN, unknown mobile-operator prefix). `isValid()` does not reject these. For strict acceptance — form submissions, payment flows — use `from()` / `tryFrom()` (which reject warning-bearing results) or call `isStrictlyValid()` explicitly. The warning path is documented per validator under `docs/en/`.

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

### Money

```php
use Eram\Abzar\Money\Amount;
use Eram\Abzar\Money\Currency;

$price = Amount::fromToman(50_000);
$price->inRials();                                 // 500000  (no ×10 confusion)
Currency::format($price->inToman());               // '۵۰،۰۰۰ تومان'
$price->add(Amount::fromToman(5_000))->inToman();  // 55000
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

### Plate numbers

```php
use Eram\Abzar\Validation\PlateNumber;

$plate = PlateNumber::from('12 ب 345 11');
$plate->letter();     // 'ب'
$plate->type()->value; // 'private'
$plate->province();   // 'تهران'
(string) $plate;      // '12ب345-11'
```

### Display formatters

```php
use Eram\Abzar\Validation\{CardNumber, PhoneNumber, Iban};

CardNumber::from('6037991234567893')->formatted();       // '6037 9912 3456 7893'
CardNumber::from('6037991234567893')->masked();          // '6037 99** **** 7893'
PhoneNumber::from('09121234567')->formatted();           // '0912 123 4567'
PhoneNumber::from('09121234567')->formatted(true);       // '+98 912 123 4567'
PhoneNumber::from('02188887777')->formatted();           // '021 8888 7777'
Iban::from('IR820540102680020817909002')->formatted();   // 'IR82 0540 1026 8002 0817 9090 02'
```

### Fixtures and extraction

```php
use Eram\Abzar\Validation\{NationalId, CardNumber, LegalId, PhoneNumber, Iban, PostalCode, PlateNumber, PlateType};

// Valid-by-construction generators (tests / seed data only — may or may not be real)
$id     = NationalId::fake();            // e.g. '0013542419'
$card   = CardNumber::fake('603799');    // Luhn-valid card with pinned BIN
$legal  = LegalId::fake();
$phone  = PhoneNumber::fake();           // e.g. '09121234567' (or pin operator: fake('912'))
$iban   = Iban::fake();                  // e.g. 'IR82054…' (or pin bank code: fake('054'))
$postal = PostalCode::fake();
$plate  = PlateNumber::fake(PlateType::TAXI); // pin category, or fake() for any

// Pull every valid ID out of free text (chat logs, OCR, scraped pages)
$ids    = NationalId::extractAll('Customer 0013542419 and 1234567891 enrolled.');
$cards  = CardNumber::extractAll('Paid via 6037 9912 3456 7893');
```

### Persian collation and half-space fixing

```php
use Eram\Abzar\Text\{PersianCollator, HalfSpaceFixer};

$c = new PersianCollator();            // requires ext-intl
$c->sort(['ج', 'ب', 'ا']);             // ['ا', 'ب', 'ج']

HalfSpaceFixer::fix('می روم');          // 'می‌روم'  (ZWNJ between prefix and verb)
HalfSpaceFixer::fix('خانه ها');         // 'خانه‌ها'
HalfSpaceFixer::fix('بزرگ ترین');       // 'بزرگ‌ترین'
```

## Further reading

Longer-form docs live under [`docs/en/`](docs/en/README.md): per-class references ([Postal Code](docs/en/postal-code.md), [Bill ID](docs/en/bill-id.md), [Keyboard Fixer](docs/en/keyboard-fixer.md), [Words to Number](docs/en/words-to-number.md), [Currency](docs/en/currency.md)), plus installation, [API stability policy](docs/en/api-stability.md), async-runtime notes, and framework integration recipes.

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
| Structured error codes | Yes | No | No |
| Bank card / IBAN / phone / national-ID / legal-ID | Yes | Yes | Subset |
| Number-to-words / time-ago / ordinals | Yes | Yes | No |
| Slug / char normalize / digit convert | Yes | Partial | No |
| WordPress integration | Via `eramhq/persian-kit` | No | No |

## Framework bridges

Abzar stays framework-agnostic. Integration recipes for Laravel FormRequest, Symfony Validator, Symfony Console, and WordPress live under [`docs/en/recipes/`](docs/en/recipes). Each is a few dozen lines — paste into your project and tweak.

## Stability

Abzar is in `0.x`. Breaking changes may happen before `1.0`; pin with `^0.5@beta` until the API stabilizes. The [API stability policy](docs/en/api-stability.md) spells out which parts of the surface are protected — `ErrorCode` values are pinned as stable API as of `0.3`.

## License

MIT. See [LICENSE](LICENSE). Parts of the validation data tables are derived from the MIT-licensed [persian-tools](https://github.com/persian-tools/persian-tools) project.
