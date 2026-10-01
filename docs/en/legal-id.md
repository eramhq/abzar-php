# Legal ID

`Eram\Abzar\Validation\LegalId` validates the 11-digit national ID of Iranian legal entities (`شناسه ملی اشخاص حقوقی`): companies, institutions and other registered organizations.

```php
use Eram\Abzar\Validation\LegalId;

LegalId::validate('10380284790')->isValid();   // true
LegalId::validate('۱۰۳۸۰۲۸۴۷۹۰')->isValid();   // true — Persian digits accepted
LegalId::validate('103-8028-4790')->isValid(); // true — dashes and spaces stripped
LegalId::validate('10380284792')->isValid();   // false — bad check digit

$legal = LegalId::tryFrom($userInput);         // LegalId or null
```

## Rules

- Persian and Arabic digits are folded to ASCII. Whitespace, dashes and invisible marks are stripped.
- Exactly 11 digits are required.
- Digits 4–9 must not all be zero.
- The last digit is a weighted checksum of the first ten. With `d` = the tenth digit + 2, sum `(d + digit[i]) × [29, 27, 23, 19, 17][i mod 5]` over the first ten digits. The check digit is that sum mod 11, with 10 folded to 0.

There are no lookups, so `LegalId` never returns a warning.

## Error codes

| Code | Kind | When |
|---|---|---|
| `LEGAL_ID.EMPTY` | error | Input is empty after normalization |
| `LEGAL_ID.WRONG_LENGTH` | error | Doesn't reduce to exactly 11 digits |
| `LEGAL_ID.MIDDLE_ZEROS` | error | Digits 4–9 are all zero |
| `LEGAL_ID.INVALID_CHECKSUM` | error | Last digit doesn't match the checksum |

## Details

```php
$legal = LegalId::from('10380284790');
$legal->value();     // '10380284790'
(string) $legal;     // '10380284790'
json_encode($legal); // {"value":"10380284790"}
```

## Fixtures

```php
LegalId::fake(); // random valid 11-digit legal ID
```

The ID is valid by construction, but it may belong to a real entity.
