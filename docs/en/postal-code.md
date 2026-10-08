---
title: "Postal code validation"
description: "Check Iranian postal-code patterns without an address lookup."
---

# Postal Code

`Eram\Abzar\Validation\PostalCode` validates Iranian 10-digit postal codes.

## Minimal example

Save beside `vendor/` and run with PHP:

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\PostalCode;

$code = PostalCode::from('۱۶۱۹۷۳۵۷۴۴');
echo $code->value(), "\n";
echo $code->zoneCode(), "\n";
```

```text
1619735744
16197
```

## More examples

```php
use Eram\Abzar\Validation\PostalCode;

PostalCode::validate('1619735744')->isValid(); // true
PostalCode::validate('16197-35744')->isValid(); // true — separators stripped
PostalCode::validate('۱۶۱۹۷۳۵۷۴۴')->isValid(); // true — Persian digits accepted
```

## Rules

- Exactly 10 digits after whitespace / dash stripping + digit normalization.
- First digit must not be `0`.
- Fifth digit must not be `0`.
- No run of 4 or more identical digits anywhere.

## Error codes

| Code | When |
|---|---|
| `POSTAL_CODE.EMPTY` | Input is empty after normalization |
| `POSTAL_CODE.WRONG_LENGTH` | Does not reduce to exactly 10 digits |
| `POSTAL_CODE.INVALID_PATTERN` | Fails first-digit, fifth-digit, or run-of-4 rules |

## Details

On success, `detail()` returns a `PostalCodeDetails` instance:

```php
$pc = PostalCode::from('1619735744');
$pc->value();        // '1619735744'
$pc->zoneCode();     // '16197' (first 5 digits — mail zone)

// Via ValidationResult:
$detail = PostalCode::validate('1619735744')->detail();
$detail->postalCode; // '1619735744'
$detail->zoneCode;   // '16197'
```

## Limitations and common mistakes

This checks a pattern, not a checksum or an address database. A passing code does not verify delivery or residence. Do not confuse any extracted ten-digit number with a confirmed postal address.

Related: [validation](validation.md), [error handling](error-handling.md), [error codes](error-codes.md).
