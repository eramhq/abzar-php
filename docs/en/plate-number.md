---
title: "Vehicle plate numbers"
description: "Parse Iranian vehicle plates and inspect type and province lookups."
---

# Plate Number

`Eram\Abzar\Validation\PlateNumber` parses Iranian car plates in the canonical `NN[letter]NNN-NN` shape: two digits, a Persian letter, three digits, then the two-digit city code. The letter gives the plate type and the city code gives the province.

## Minimal example

Save beside `vendor/` and run with PHP:

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\PlateNumber;

$plate = PlateNumber::from('۱۲ ب ۳۴۵ - ۱۱');
echo $plate->value(), "\n";
echo $plate->province(), "\n";
```

```text
12ب345-11
تهران
```

## More examples

```php
use Eram\Abzar\Validation\PlateNumber;

PlateNumber::validate('12ب345-11')->isValid();     // true
PlateNumber::validate('12 ب 345 - 11')->isValid(); // true — spaces and dash optional
PlateNumber::validate('۱۲ب۳۴۵۱۱')->isValid();      // true — Persian digits accepted
PlateNumber::validate('12ب34-11')->isValid();      // false — three digits required after the letter

$plate = PlateNumber::tryFrom('12ب345-11');         // PlateNumber or null
```

## Rules

- Persian and Arabic digits are folded to ASCII. Whitespace, dashes and invisible marks are stripped.
- The shape must be two digits, a 1–3 character letter slot (`الف` is the longest), three digits and two digits.
- Arabic-keyboard `ي` / `ك` in the letter slot are folded to Persian `ی` / `ک`.

## Warnings

Both lookups degrade to a warning rather than an error, so a well-formed plate is always valid:

| Input | Result |
|---|---|
| Letter not in the table, e.g. `12غ345-11` | valid, `PLATE_NUMBER.UNKNOWN_LETTER`, `type()` is `PlateType::OTHER` |
| City code not in the table, e.g. `12ب345-00` | valid, `PLATE_NUMBER.UNKNOWN_CITY_CODE`, `province()` is `null` |

A plate can carry both warnings. Check `isStrictlyValid()` when only catalogued letters and codes are acceptable.

## Error codes

| Code | Kind | When |
|---|---|---|
| `PLATE_NUMBER.EMPTY` | error | Input is empty after normalization |
| `PLATE_NUMBER.INVALID_FORMAT` | error | Not shaped `NN[letter]NNN-NN` |
| `PLATE_NUMBER.UNKNOWN_LETTER` | warning | Letter isn't in the plate-type table |
| `PLATE_NUMBER.UNKNOWN_CITY_CODE` | warning | City code isn't in the province table |

## Details

```php
$plate = PlateNumber::from('12ب345-11');
$plate->value();        // '12ب345-11'
$plate->twoDigit();     // '12'
$plate->letter();       // 'ب'
$plate->threeDigit();   // '345'
$plate->cityCode();     // '11'
$plate->type();         // PlateType::PRIVATE
$plate->province();     // 'تهران'
$plate->provinceEnum(); // Province::TEHRAN
```

`PlateType` covers `PRIVATE`, `TAXI` (`ت`), `PUBLIC` (`ع`), `POLICE` (`پ`), `GOVERNMENT` (`الف`), `AGRICULTURAL` (`ک`), `DISABLED` (`ژ`), `MILITARY` (`ش` `ث` `ز` `ف`), `DIPLOMATIC`, `TEMPORARY` (`گ`) and `OTHER` for unknown letters.

### Codes shared across a province split

Some city codes were issued before a province split and still map to every successor province. `provinceEnum()` is `null` for those. Use `provinces()` / `provinceEnums()` instead:

```php
$plate = PlateNumber::from('12ب345-21');
$plate->province();      // 'تهران - البرز'
$plate->provinces();     // ['تهران', 'البرز']
$plate->provinceEnum();  // null
$plate->provinceEnums(); // [Province::TEHRAN, Province::ALBORZ]
```

The table follows the persian-tools numberplate dataset, cross-checked against Persian Wikipedia. The differences are listed in the header of [`src/Data/PlateCodes.php`](../../src/Data/PlateCodes.php).

## Extracting from text

```php
PlateNumber::extractAll('پلاک ۱۲ ب ۳۴۵ - ۱۱ ثبت شد'); // [PlateNumber('12ب345-11')]
```

## Fixtures

```php
use Eram\Abzar\Validation\PlateType;

PlateNumber::fake();               // random known letter and city code
PlateNumber::fake(PlateType::TAXI); // e.g. '15ت075-14'
```

`fake(PlateType::OTHER)` throws `ValidationException` (`FAKE.INVALID_ARGUMENT`), because `OTHER` stands for unknown letters, not a real category.

## Limitations and common mistakes

This is a shape parser and bundled lookup, not a vehicle registry or ownership check. The implementation accepts a 1–3 Unicode-letter slot, with unknown letters producing a warning. It does not support every special vehicle plate layout.

Related: [validation](validation.md), [error handling](error-handling.md), [error codes](error-codes.md).
