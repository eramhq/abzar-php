---
title: "Validation"
description: "Choose result objects or value objects and distinguish validity from verification."
---

# Validation

Use validators at input boundaries to normalize Iranian identifiers and report invalid structure or checksums. Keep identifiers as strings so leading zeros survive. Persian, Arabic and ASCII digits are accepted by validators.

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\CardNumber;

$result = CardNumber::validate('1234567890123452');
var_dump($result->isValid(), $result->isStrictlyValid());
echo $result->warningCodes()[0]->value, "\n";
var_dump(CardNumber::from('1234567890123452')->bank());
```

```text
bool(true)
bool(false)
CARD_NUMBER.UNKNOWN_BIN
NULL
```

## Pick an entry point

| Method | Valid input | Invalid input |
|---|---|---|
| `validate($input)` | `ValidationResult`, possibly with warnings | Result with errors |
| `from($input)` | Immutable value object | Throws `ValidationException` |
| `tryFrom($input)` | Value object | `null` |

`BillId` is the exception to the single-input signature: `validate($billId)` checks one field, while `from($billId, $paymentId)`, `tryFrom($billId, $paymentId)` and `validatePair()` require both fields.

Warnings mean a bundled lookup did not resolve. They do not prevent object construction or extraction. `isStrictlyValid()` requires validity and zero warnings, but still does not verify the identifier with an external authority. Unknown plate letters yield `PlateType::OTHER`; other missing lookups generally yield `null`.

## Choose the validator

- [National ID](national-id.md) and [legal ID](legal-id.md): length and checksum rules.
- [Card number](card-number.md) and [IBAN](iban.md): checksums and issuer lookups.
- [Phone number](phone-number.md): mobile/landline structure and prefix lookup, no checksum.
- [Postal code](postal-code.md): pattern rules, no address database or checksum.
- [Bill ID](bill-id.md): bill checksum and payment cross-checksums.
- [Plate number](plate-number.md): structure, type and province lookup, no registry check.

## Common mistakes

A valid value may be unassigned, inactive or belong to someone else. Identity checks, OTP delivery, ownership checks and payment confirmation belong to separate services. Operator prefixes do not establish the current carrier after number portability. City/province metadata is not a person's current address.

`fake()` produces structurally valid test data, not reserved fictional identifiers. Generated values may coincide with real identifiers. `extractAll()` is only available on NationalId, CardNumber, Iban, PhoneNumber, PostalCode and PlateNumber; matches are candidates, not proof of what a number means.

Related: [errors and warnings](error-handling.md), [error codes](error-codes.md), [framework integration](framework-integration.md).
