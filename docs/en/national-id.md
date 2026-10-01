# National ID

`Eram\Abzar\Validation\NationalId` validates the Iranian national ID (`کد ملی`): 10 digits ending in a mod-11 check digit, whose first three digits name the issuing city.

```php
use Eram\Abzar\Validation\NationalId;

NationalId::validate('0013542419')->isValid();   // true
NationalId::validate('۰۰۱۳۵۴۲۴۱۹')->isValid();   // true — Persian digits accepted
NationalId::validate('001-354241-9')->isValid(); // true — dashes and spaces stripped
NationalId::validate('1234567890')->isValid();   // false — bad check digit

$id = NationalId::tryFrom($userInput);           // NationalId or null
$id = NationalId::from($userInput);              // NationalId, or throws ValidationException
```

## Rules

- Persian and Arabic digits are folded to ASCII. Whitespace (including NBSP), dashes and invisible marks (ZWNJ, bidi marks) are stripped.
- 8 or 9 digits are rejected as `LIKELY_TRUNCATED`. Leading zeros are commonly lost in CSV, Excel or `intval()` round-trips. abzar doesn't pad them back, because that would hide the upstream bug. `str_pad($id, 10, '0', STR_PAD_LEFT)` before retrying if you know the source dropped them.
- Exactly 10 digits are required.
- All-same digits (`1111111111`) and `0123456789` are rejected.
- Digits 4–9 must not all be zero.
- The last digit must match the mod-11 check digit of the first nine.

## Warnings

A checksum-valid ID whose 3-digit prefix isn't in the bundled city table is **valid** with a warning, and `city` / `province` are `null`:

```php
$r = NationalId::validate('2540201288');
$r->isValid();         // true
$r->isStrictlyValid(); // false — has a warning
$r->warningCodes();    // [ErrorCode::NATIONAL_ID_UNKNOWN_CITY_CODE]
```

`from()`, `tryFrom()` and `extractAll()` accept warning-bearing IDs. Check `isStrictlyValid()` first if only catalogued prefixes are acceptable.

## Error codes

| Code | Kind | When |
|---|---|---|
| `NATIONAL_ID.EMPTY` | error | Input is empty after normalization |
| `NATIONAL_ID.LIKELY_TRUNCATED` | error | 8 or 9 digits, probably with leading zeros lost |
| `NATIONAL_ID.WRONG_LENGTH` | error | Doesn't reduce to exactly 10 digits |
| `NATIONAL_ID.ALL_SAME_DIGITS` | error | All ten digits are the same |
| `NATIONAL_ID.SEQUENTIAL_DIGITS` | error | `0123456789` |
| `NATIONAL_ID.MIDDLE_ZEROS` | error | Digits 4–9 are all zero |
| `NATIONAL_ID.INVALID_CHECKSUM` | error | Last digit doesn't match the mod-11 check digit |
| `NATIONAL_ID.UNKNOWN_CITY_CODE` | warning | Valid, but the 3-digit prefix isn't in the city table |

`ALL_SAME_DIGITS`, `SEQUENTIAL_DIGITS`, `MIDDLE_ZEROS` and `INVALID_CHECKSUM` share the Persian message `کد ملی نامعتبر است`; the code tells them apart. See [Error codes](error-codes.md) for every message.

## Details

```php
$id = NationalId::from('0013542419');
$id->value();        // '0013542419'
$id->cityCode();     // '001'
$id->city();         // 'تهران مرکزی'
$id->province();     // 'تهران'
$id->provinceEnum(); // Province::TEHRAN
(string) $id;        // '0013542419'
json_encode($id, JSON_UNESCAPED_UNICODE); // {"value":"0013542419","city_code":"001","city":"تهران مرکزی","province":"تهران"}

// Via ValidationResult:
$detail = NationalId::validate('0013542419')->detail();
$detail->cityCode;   // '001'
```

## Extracting from text

`extractAll()` scans free text for 10-digit runs, in Persian or ASCII digits, and returns each valid ID, left to right:

```php
NationalId::extractAll('کد ملی: ۰۰۱۳۵۴۲۴۱۹ و 1234567890'); // [NationalId('0013542419')]
```

A bare 10-digit run may equally be a postal code, so expect overlap when you scan mixed text with both.

## Fixtures

```php
NationalId::fake();      // random checksum-valid ID
NationalId::fake('001'); // pinned to the Tehran prefix
```

`fake()` throws `ValidationException` (`FAKE.INVALID_ARGUMENT`) when the prefix isn't exactly three digits. The generated ID is valid by construction, but it may belong to a real person, so keep it out of production data.
