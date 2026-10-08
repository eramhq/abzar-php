---
title: "Card number validation"
description: "Check card numbers with Luhn and look up the bank from the BIN."
---

# Card Number

`Eram\Abzar\Validation\CardNumber` validates 16-digit Iranian bank cards (Shetab) with the Luhn checksum and names the issuing bank from the 6-digit BIN.

## Minimal example

Save beside `vendor/` and run with PHP:

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\CardNumber;

$card = CardNumber::from('6037-7016-8909-5443');
echo $card->bank(), "\n";
echo $card->masked(), "\n";
```

```text
بانک کشاورزی
6037 70** **** 5443
```

## More examples

```php
use Eram\Abzar\Validation\CardNumber;

CardNumber::validate('6037701689095443')->isValid();    // true
CardNumber::validate('6037-7016-8909-5443')->isValid(); // true — separators stripped
CardNumber::validate('۶۰۳۷۷۰۱۶۸۹۰۹۵۴۴۳')->isValid();    // true — Persian digits accepted
CardNumber::validate('6037701689095444')->isValid();    // false — Luhn fails

$card = CardNumber::tryFrom('6037701689095443');                // CardNumber or null
```

## Rules

- Persian and Arabic digits are folded to ASCII. Whitespace, dashes and invisible marks are stripped.
- Exactly 16 digits are required.
- All-same digits (`0000000000000000`) are rejected. They pass Luhn, but no real card has that shape.
- The Luhn checksum must pass.

## Warnings

A Luhn-valid card whose BIN isn't in the bundled table is **valid** with a warning and `bank: null`. New and co-branded BINs appear faster than any table is updated, and rejecting them would block real cards.

```php
$r = CardNumber::validate('1234567890123452');
$r->isValid();         // true
$r->isStrictlyValid(); // false
$r->warningCodes();    // [ErrorCode::CARD_NUMBER_UNKNOWN_BIN]
CardNumber::from('1234567890123452')->bank(); // null
```

Check `isStrictlyValid()` before `from()` when only cards from a known issuer are acceptable.

## Error codes

| Code | Kind | When |
|---|---|---|
| `CARD_NUMBER.EMPTY` | error | Input is empty after normalization |
| `CARD_NUMBER.WRONG_LENGTH` | error | Doesn't reduce to exactly 16 digits |
| `CARD_NUMBER.ALL_SAME_DIGITS` | error | All sixteen digits are the same |
| `CARD_NUMBER.INVALID_CHECKSUM` | error | Luhn checksum fails |
| `CARD_NUMBER.UNKNOWN_BIN` | warning | Valid, but the BIN isn't in the bank table |

## Details

```php
$card = CardNumber::from('6037701689095443');
$card->value();     // '6037701689095443'
$card->bin();       // '603770'
$card->bank();      // 'بانک کشاورزی'
$card->bankEnum();  // Bank::KESHAVARZI
$card->formatted(); // '6037 7016 8909 5443'
$card->masked();    // '6037 70** **** 5443'
json_encode($card, JSON_UNESCAPED_UNICODE); // {"value":"6037701689095443","bin":"603770","bank":"بانک کشاورزی"}
```

`masked()` shows the first 6 and last 4 digits and hides the middle 6. Masking alone is not a compliance guarantee; choose what your application may display or log. `(string) $card` and `json_encode()` carry the full number.

## Extracting from text

`extractAll()` scans for 16-digit runs, optionally grouped with single spaces or dashes, and returns each valid card. Unknown-BIN cards are included:

```php
CardNumber::extractAll('کارت 6037 7016 8909 5443 و 6037701689095444'); // [CardNumber('6037701689095443')]
```

## Fixtures

```php
CardNumber::fake();         // Luhn-valid card with a random known BIN
CardNumber::fake('603770'); // pinned BIN
```

`fake()` throws `ValidationException` (`FAKE.INVALID_ARGUMENT`) when the BIN isn't exactly six digits. The cards pass Luhn but are not reserved test numbers; they may coincide with real cards.

## Limitations and common mistakes

Luhn validation does not establish that a card exists, is active, or belongs to a customer. A known BIN is only bundled issuer metadata. Keep the number as a string and avoid logging full values.

Related: [validation](validation.md), [error handling](error-handling.md), [error codes](error-codes.md).
