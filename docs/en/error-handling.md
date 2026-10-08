---
title: "Errors and warnings"
description: "Handle invalid input, non-fatal lookup warnings and library exceptions."
---

# Errors and warnings

Use result objects for expected validation failures, stable codes for branching, and exception types for operations that throw.

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Exception\ValidationException;
use Eram\Abzar\Validation\CardNumber;
use Eram\Abzar\Validation\NationalId;

$result = CardNumber::validate('1234567890123452');
echo $result->isValid() ? "accepted\n" : "rejected\n";
echo $result->warningCodes()[0]->value, "\n";

try {
    NationalId::from('1234567890');
} catch (ValidationException $e) {
    echo $e->errorCode()->value, "\n";
    echo $e->result()->errors()[0], "\n";
}
```

```text
accepted
CARD_NUMBER.UNKNOWN_BIN
NATIONAL_ID.INVALID_CHECKSUM
کد ملی نامعتبر است
```

## Choose what to handle

- `errors()` / `errorCodes()`: invalid input. Inspect `detail()` only after checking success; invalid results normally have no detail.
- `warnings()` / `warningCodes()`: valid input with unresolved lookup metadata. `from()` and `tryFrom()` accept it. Use `isStrictlyValid()` when your business rule requires all lookups resolved.
- `ValidationException`: invalid `from()` input or a bad fixture-generator argument. `result()` gives the full validation result.
- `FormatException`: invalid formatter input or failed HTML segmentation.
- `MoneyException`: negative or overflowing `Amount` operations.
- `EnvironmentException`: optional `intl` support missing for NFC/collation.

All four exceptions extend `Eram\Abzar\Exception\AbzarException`, which exposes `errorCode()`. Native PHP contract failures such as wrong argument types can still throw `TypeError`; the base class does not catch every PHP failure.

Do not compare Persian messages: their wording can change. Read enum cases or their backing values. `WordsToNumber::parse()` uses `null` for unparseable input instead of throwing. Treat identifiers and exception input excerpts as sensitive when choosing what to log.

Related: [error-code reference](error-codes.md), [validation](validation.md), [stability](api-stability.md).
