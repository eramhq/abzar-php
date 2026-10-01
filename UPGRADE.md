# Upgrade guide

## 0.7 → 0.8

0.8 makes the validators behave the same way as each other. Most apps need only the first two sections. Every change is also listed in the [CHANGELOG](CHANGELOG.md).

### 1. `from()` / `tryFrom()` accept warning-bearing results

This reverses 0.5. `CardNumber`, `PhoneNumber` and `PlateNumber` used to reject a result that was valid but carried a warning (unknown BIN, operator, area code, plate letter or city code). Now every validator builds the value object whenever `validate()->isValid()` is true. The lookup accessors return `null` (or `PlateType::OTHER`) when the lookup failed.

| Input | 0.7 | 0.8 |
|---|---|---|
| `CardNumber::from('1234567890123452')` (Luhn-valid, unknown BIN) | throws `CARD_NUMBER.UNKNOWN_BIN` | VO, `bank()` is `null` |
| `PhoneNumber::tryFrom('09401234567')` (unknown operator) | `null` | VO, `operator()` is `null` |
| `PlateNumber::from('12ح345-80')` (unknown letter + city code) | throws | VO, `type()` is `OTHER`, `province()` is `null` |
| `CardNumber::extractAll($text)` | known-BIN cards only | every Luhn-valid card |

If you relied on the strict behaviour, for example to accept only cards from a known issuer, check `isStrictlyValid()` first:

```php
use Eram\Abzar\Validation\CardNumber;

// 0.7
$card = CardNumber::from($input);

// 0.8, same strictness
$result = CardNumber::validate($input);
$card = $result->isStrictlyValid()
    ? CardNumber::from($input)
    : throw \Eram\Abzar\Exception\ValidationException::fromResult($result);

// …or simply reject a null lookup
$card = CardNumber::from($input);
if ($card->bank() === null) {
    // unknown issuer
}
```

`isStrictlyValid()` is unchanged and still means "valid and no warnings".

### 2. Unknown lookups are always warnings

The same rule now applies to every validator: an input that is well-formed and passes its checksum is valid. If a lookup table doesn't know it, the result also carries a warning and the lookup field is `null`.

| Validator | Case | 0.7 | 0.8 |
|---|---|---|---|
| `NationalId` | unknown 3-digit city prefix | `valid`, no warning | `validWithWarnings(NATIONAL_ID.UNKNOWN_CITY_CODE)` |
| `Iban` | unknown 3-digit bank code | `valid`, no warning | `validWithWarnings(IBAN.UNKNOWN_BANK)` |
| `PhoneNumber` | landline with uncatalogued area code (`0[1-8]x`) | **invalid** `PHONE_NUMBER.INVALID_FORMAT` | `validWithWarnings(PHONE_NUMBER.UNKNOWN_AREA_CODE)`, `city`/`province` `null` |
| `CardNumber` | all-same-digit card (`0000…`) | invalid `CARD_NUMBER.INVALID_CHECKSUM` | invalid `CARD_NUMBER.ALL_SAME_DIGITS` |

If you treated `isValid()` as "fully resolved", switch those call sites to `isStrictlyValid()`. If you matched `INVALID_FORMAT` to detect unknown area codes, or `INVALID_CHECKSUM` to detect all-same-digit cards, match the new codes instead.

`PhoneNumber` also stops accepting 11-digit numbers that start with `00` as landlines; they now return `INVALID_FORMAT`.

### 3. Exceptions

| Where | 0.7 | 0.8 |
|---|---|---|
| `::fake()` with a malformed pin (`NationalId`, `CardNumber`, `Iban`, `PhoneNumber`, `PlateNumber`) | `\InvalidArgumentException` | `ValidationException` with `ErrorCode::FAKE_INVALID_ARGUMENT` |
| `Money\Amount` (negative, overflow) | `FormatException` | `Exception\MoneyException` (same `AMOUNT_*` codes) |
| `ValidationException::fromResult()` on a result with no `ErrorCode` | `\LogicException` | exception with `ErrorCode::VALIDATION_FAILED` |

Code that catches `AbzarException` needs no change. Update any `catch (FormatException)` around `Amount` and any `catch (\InvalidArgumentException)` around `fake()`.

### 4. Signature changes

- `OrdinalNumber::toShort(int $n, bool $persianDigits = true, string $suffix = 'ام')`. The second argument was the string `'persian'` / `'english'`:

  ```php
  OrdinalNumber::toShort(43, 'english', 'rd');  // 0.7
  OrdinalNumber::toShort(43, false, 'rd');      // 0.8
  OrdinalNumber::toShort(43, persianDigits: false);
  ```

- `PhoneNumber::fake()` gains `PhoneNumberType $type = MOBILE` and `?string $areaCode = null`. Existing calls are unaffected.
- `PhoneNumberDetails::landline()` now accepts `?string` for `$city` and `$province`.

### 5. Behaviour changes in formatters and text tools

- `OrdinalNumber::toWord(30)` / `addSuffix('سی')` return `سی‌ام` (joined with ZWNJ) instead of `سی اُم`.
- `KeyboardFixer::enToFa()` maps upper-case letters through the ISIRI 9147 Shift layer instead of lower-casing them. For example `H` gives `آ` and `C` gives `ژ`, so `'SGHL'` no longer becomes `'سلام'`. `faToEn()` reverses the Shift layer too. If you relied on caps-lock tolerance, lower-case the input first: `KeyboardFixer::enToFa(strtolower($typed))`.
- `BillId` rejects bill IDs longer than 13 digits with `BILL_ID.WRONG_LENGTH` (the old limit was 18). Payment IDs still accept up to 18 digits.
- `Bank::AYANDEH->isDefunct()` is `true`: Ayandeh was dissolved into Bank Melli on 2025-10-23.

### 6. Additions you may want

- `extractAll()` on `PhoneNumber`, `Iban`, `PostalCode` and `PlateNumber`.
- `masked()` on `PhoneNumber` (`0912 *** 4567`) and `Iban` (`IR82 054* **** **** **** **90 02`).
- `PlateNumber::value()`, `provinces()`, `provinceEnum()` and `provinceEnums()`.
- `Currency::format()` accepts a `Money\Amount`.
- `BillId::fake(?BillType)` and `BillId::fakePaymentId(string $billId)`.
- `PhoneNumber::fake(type: PhoneNumberType::LANDLINE, areaCode: '021')`.
