---
title: "Abzar overview"
description: "Persian utilities for PHP, with a quick start and links to every guide."
---

# Abzar overview

Abzar provides framework-independent Iranian validators, Persian text and digit tools, formatters, and money value objects. It requires PHP 8.1+ and `mbstring`, with no third-party Composer runtime dependencies. `intl` is optional for collation and NFC. See [installation](installation.md).

## Quick start

After installing, save this as `example.php` beside `vendor/` and run `php example.php`:

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\PhoneNumber;
use Eram\Abzar\Money\Amount;
use Eram\Abzar\Money\Currency;

$phone = PhoneNumber::from('+98 912 123 4567');
echo $phone->value(), "\n";
echo Currency::format(Amount::fromRials(12_345)), "\n";
```

```text
09121234567
۱،۲۳۴.۵ تومان
```

The displayed amount keeps the five-rial remainder. `Amount::inToman()` would return only the integer toman portion. Invalid input to `PhoneNumber::from()` throws; use `validate()` for form errors. See [money](currency.md) and [validation](validation.md).

## Choose a guide

- [Validation](validation.md): structure, checksums, lookup details and warnings.
- [Persian text](persian-text.md), [digits](digits.md), [keyboard fixes](keyboard-fixer.md).
- [Number formatting](formatting.md), [words to number](words-to-number.md), [currency](currency.md).
- [Error handling](error-handling.md) and the [error-code reference](error-codes.md).
- [Framework integration](framework-integration.md) and [long-running workers](async-runtimes.md).
- [API stability](api-stability.md), [persian-tools differences](persian-tools-parity.md), [related projects](related.md).

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


## Boundaries

Validators do not contact registries or payment providers and cannot establish identity, ownership, assignment, or reachability. Prefix lookups are bundled snapshots. Text normalization does not index or search documents. Abzar does not provide a Jalali calendar or framework bridges. APIs remain pre-1.0; read the [stability policy](api-stability.md).

All standalone snippets assume Composer's autoloader has been loaded. Comments after expressions show return values; an expression alone does not print. Complete examples with a following `text` block print exactly that output. Framework snippets run inside the named application.
