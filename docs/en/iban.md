---
title: "IBAN validation"
description: "Validate Iranian Sheba numbers and inspect bank details."
---

# IBAN (Sheba)

`Eram\Abzar\Validation\Iban` validates Iranian IBANs (`شماره شبا`): `IR`, two check digits, a 3-digit bank code and 19 account digits, checked with ISO 13616 mod-97.

## Minimal example

Save beside `vendor/` and run with PHP:

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\Iban;

$iban = Iban::from('IR820540102680020817909002');
echo $iban->bank(), "\n";
echo $iban->formatted(), "\n";
```

```text
بانک پارسیان
IR82 0540 1026 8002 0817 9090 02
```

## More examples

```php
use Eram\Abzar\Validation\Iban;

Iban::validate('IR820540102680020817909002')->isValid();         // true
Iban::validate('ir82 0540 1026 8002 0817 9090 02')->isValid();   // true — case and spacing normalized
Iban::validate('820540102680020817909002')->isValid();           // true — IR prefix added to 24 bare digits
Iban::validate('IR۸۲۰۵۴۰۱۰۲۶۸۰۰۲۰۸۱۷۹۰۹۰۰۲')->isValid();         // true — Persian digits accepted
Iban::validate('IR820540102680020817909003')->isValid();         // false — mod-97 fails

$iban = Iban::tryFrom('IR820540102680020817909002');                               // Iban or null
```

## Rules

- Persian and Arabic digits are folded to ASCII, the input is upper-cased, and whitespace, dashes and invisible marks are stripped.
- 24 bare digits are read as an Iranian IBAN without its `IR` prefix.
- A two-letter prefix other than `IR` is rejected as `MISSING_PREFIX`. abzar validates Iranian IBANs only.
- Exactly `IR` + 24 digits are required.
- The ISO 13616 mod-97 checksum must equal 1.

## Warnings

A checksum-valid IBAN whose bank code isn't in the bundled table is **valid** with an `IBAN.UNKNOWN_BANK` warning, and `bank()` is `null`. Check `isStrictlyValid()` when only catalogued banks are acceptable.

## Error codes

| Code | Kind | When |
|---|---|---|
| `IBAN.EMPTY` | error | Input is empty after normalization |
| `IBAN.MISSING_PREFIX` | error | Starts with a country code other than `IR` |
| `IBAN.WRONG_LENGTH` | error | Not `IR` + exactly 24 digits |
| `IBAN.INVALID_CHECKSUM` | error | mod-97 checksum fails |
| `IBAN.UNKNOWN_BANK` | warning | Valid, but the bank code isn't in the bank table |

## Details

```php
$iban = Iban::from('IR820540102680020817909002');
$iban->value();     // 'IR820540102680020817909002'
$iban->bankCode();  // '054'
$iban->bank();      // 'بانک پارسیان'
$iban->bankEnum();  // Bank::PARSIAN
$iban->formatted(); // 'IR82 0540 1026 8002 0817 9090 02'
$iban->masked();    // 'IR82 054* **** **** **** **90 02'
json_encode($iban, JSON_UNESCAPED_UNICODE); // {"value":"IR820540102680020817909002","bank_code":"054","bank":"بانک پارسیان"}
```

`masked()` keeps `IR`, the check digits, the bank code and the last four digits.

## Extracting from text

`extractAll()` scans for `IR` + 24 digits (case-insensitive, optionally grouped with spaces or dashes) and returns each valid IBAN:

```php
Iban::extractAll('شبا: IR82 0540 1026 8002 0817 9090 02'); // [Iban('IR820540102680020817909002')]
```

## Fixtures

```php
Iban::fake();      // valid IBAN with a random known bank code
Iban::fake('054'); // pinned bank code
```

`fake()` computes real mod-97 check digits, so the result round-trips through `validate()`. It throws `ValidationException` (`FAKE.INVALID_ARGUMENT`) when the bank code isn't exactly three digits.

## Limitations and common mistakes

Only Iranian IBANs are supported. A passing checksum does not establish account existence, status or ownership. Bare 24-digit input works with validation, but extraction requires the IR prefix.

Related: [validation](validation.md), [error handling](error-handling.md), [error codes](error-codes.md).
