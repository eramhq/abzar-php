---
title: "Phone number validation"
description: "Validate and normalize Iranian mobile and landline numbers."
---

# Phone Number

`Eram\Abzar\Validation\PhoneNumber` validates Iranian mobile and landline numbers, normalizes them to local (`09121234567`) and E.164 (`+989121234567`) form, and looks up the mobile operator or the landline's city.

## Minimal example

Save beside `vendor/` and run with PHP:

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\PhoneNumber;

$phone = PhoneNumber::from('+98 912 123 4567');
echo $phone->value(), "\n";
echo $phone->e164(), "\n";
```

```text
09121234567
+989121234567
```

## More examples

```php
use Eram\Abzar\Validation\PhoneNumber;

PhoneNumber::validate('09121234567')->isValid();      // true
PhoneNumber::validate('+98 912 123 4567')->isValid(); // true
PhoneNumber::validate('00989121234567')->isValid();   // true
PhoneNumber::validate('9121234567')->isValid();       // true — leading 0 omitted
PhoneNumber::validate('۰۹۱۲۱۲۳۴۵۶۷')->isValid();      // true — Persian digits accepted
PhoneNumber::validate('(021) 8888-7777')->isValid();  // true — landline
PhoneNumber::validate('0912123456')->isValid();       // false — one digit short

PhoneNumber::normalize('+98 912 123 4567');           // '09121234567'
PhoneNumber::normalize('abc');                        // null
```

## Rules

- Persian and Arabic digits are folded to ASCII. Whitespace, dashes, parentheses, dots and invisible marks are stripped.
- A `+98`, `0098` or (12-digit) `98` prefix is replaced with `0`. A 10-digit number starting with `9`, or with a known area code, gets the missing leading `0`.
- **Mobile:** `09` + 9 digits.
- **Landline:** `0[1-8]` + 9 digits, where the first three digits are the area code. A number starting `00` is an international prefix, not a landline.

## Warnings

Both lookups degrade to a warning rather than an error:

| Input | Result |
|---|---|
| Mobile prefix not in the operator table, e.g. `09802002580` | valid, `PHONE_NUMBER.UNKNOWN_OPERATOR`, `operator()` is `null` |
| Landline area code not in the table, e.g. `01234567890` | valid, `PHONE_NUMBER.UNKNOWN_AREA_CODE`, `city()` / `province()` are `null` |

New MVNO prefixes appear faster than any table is updated. `from()`, `tryFrom()` and `extractAll()` accept these numbers; check `isStrictlyValid()` when only catalogued operators or area codes are acceptable.

## Error codes

| Code | Kind | When |
|---|---|---|
| `PHONE_NUMBER.EMPTY` | error | Input is empty after normalization |
| `PHONE_NUMBER.INVALID_FORMAT` | error | Not a mobile or landline shape after normalization |
| `PHONE_NUMBER.UNKNOWN_OPERATOR` | warning | Mobile, but the `09xx` prefix isn't in the operator table |
| `PHONE_NUMBER.UNKNOWN_AREA_CODE` | warning | Landline, but the area code isn't in the table |

## Details

```php
$mobile = PhoneNumber::from('09121234567');
$mobile->value();          // '09121234567'
$mobile->e164();           // '+989121234567'
$mobile->formatted();      // '0912 123 4567'
$mobile->formatted(true);  // '+98 912 123 4567'
$mobile->masked();         // '0912 *** 4567'
$mobile->type();           // PhoneNumberType::MOBILE
$mobile->isMobile();       // true
$mobile->operator();       // 'همراه اول'
$mobile->operatorEnum();   // Operator::MCI

$landline = PhoneNumber::from('02188887777');
$landline->formatted();     // '021 8888 7777'
$landline->formatted(true); // '+98 21 8888 7777'
$landline->masked();        // '021 **** 7777'
$landline->isLandline();    // true
$landline->areaCode();      // '021'
$landline->city();          // 'تهران'
$landline->province();      // 'تهران'
$landline->provinceEnum();  // Province::TEHRAN
```

Operator accessors return `null` for a landline, and area-code accessors return `null` for a mobile. `json_encode()` carries `type`, `normalized_local` and `normalized_e164`, then `operator` for a mobile or `area_code` / `city` / `province` for a landline.

## Extracting from text

`extractAll()` finds numbers written with a leading `0`, `+98` or `0098`, optionally grouped with spaces or dashes. Bare 10-digit runs are skipped as too ambiguous:

```php
PhoneNumber::extractAll('تماس: ۰۹۱۲ ۱۲۳ ۴۵۶۷ یا +98 21 8888 7777'); // [PhoneNumber('09121234567'), PhoneNumber('02188887777')]
```

## Fixtures

```php
use Eram\Abzar\Validation\PhoneNumberType;

PhoneNumber::fake();                                         // random catalogued mobile
PhoneNumber::fake('912');                                    // pinned operator prefix
PhoneNumber::fake(type: PhoneNumberType::LANDLINE);          // random catalogued landline
PhoneNumber::fake(type: PhoneNumberType::LANDLINE, areaCode: '021');
```

`fake()` throws `ValidationException` (`FAKE.INVALID_ARGUMENT`) when a pin is malformed or doesn't match the type, e.g. an operator prefix for a landline. The number is valid by construction, but it may belong to a real subscriber.

## Limitations and common mistakes

No OTP is sent and no subscriber or reachability check is made. Operator lookup is based on the prefix table, not a live carrier query; number portability can make it differ from the current carrier. Supply the country/area prefix explicitly for ambiguous numbers.

Related: [validation](validation.md), [error handling](error-handling.md), [error codes](error-codes.md).
