# Upgrade guide

## 0.8.0-beta → 0.8.1

No PHP implementation or API changes. This release updates documentation, translations and documentation checks; no code migration is needed.

Use `composer require 'eram/abzar:^0.8.1'` to require this release or later `0.8` patches below `0.9`. The beta stability flag is no longer needed for this release. `0.8.1` is a regular release without a prerelease suffix, but the project remains pre-1.0: minor releases may still introduce breaking changes. See the [API stability policy](docs/en/api-stability.md).

Composer infers the package version from the `0.8.1` tag; no `version` field is added to `composer.json`.

## 0.7 → 0.8

No breaking changes. Bump the constraint to `^0.8@beta`.

Two outputs widen, which only matters if you assert on them:

- `WordsToNumber::parse()` now reads `شیش`, `چارصد`, `بیلیون` and `کوآدریلیون`, which used to return `null`.
- `HalfSpaceFixer::fix()` now binds a suffix that is followed by a closing bracket, quote or colon: `(آبی تر)` becomes `(آبی‌تر)` (before, it was left unchanged).

New in 0.8: `Amount::toWords()`. See the [CHANGELOG](CHANGELOG.md).

## 0.6 → 0.7

0.7 fixes a batch of data and input-handling bugs and makes the validators behave the same way as each other. Most apps need only the first two sections; section 6 lists bug fixes that change output you may have stored or asserted on. Every change is also listed in the [CHANGELOG](CHANGELOG.md).

### 1. `from()` / `tryFrom()` accept warning-bearing results

This reverses 0.5. `CardNumber`, `PhoneNumber` and `PlateNumber` used to reject a result that was valid but carried a warning (unknown BIN, operator, area code, plate letter or city code). Now every validator builds the value object whenever `validate()->isValid()` is true. The lookup accessors return `null` (or `PlateType::OTHER`) when the lookup failed.

| Input | 0.6 | 0.7 |
|---|---|---|
| `CardNumber::from('1234567890123452')` (Luhn-valid, unknown BIN) | throws `CARD_NUMBER.UNKNOWN_BIN` | VO, `bank()` is `null` |
| `PhoneNumber::tryFrom('09401234567')` (unknown operator) | `null` | VO, `operator()` is `null` |
| `PlateNumber::from('12ح345-80')` (unknown letter + city code) | throws | VO, `type()` is `OTHER`, `province()` is `null` |
| `CardNumber::extractAll($text)` | known-BIN cards only | every Luhn-valid card |

If you relied on the strict behaviour, for example to accept only cards from a known issuer, check `isStrictlyValid()` first:

```php
use Eram\Abzar\Validation\CardNumber;

// 0.6
$card = CardNumber::from($input);

// 0.7, same strictness
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

| Validator | Case | 0.6 | 0.7 |
|---|---|---|---|
| `NationalId` | unknown 3-digit city prefix | `valid`, no warning | `validWithWarnings(NATIONAL_ID.UNKNOWN_CITY_CODE)` |
| `Iban` | unknown 3-digit bank code | `valid`, no warning | `validWithWarnings(IBAN.UNKNOWN_BANK)` |
| `PhoneNumber` | landline with uncatalogued area code (`0[1-8]x`) | **invalid** `PHONE_NUMBER.INVALID_FORMAT` | `validWithWarnings(PHONE_NUMBER.UNKNOWN_AREA_CODE)`, `city`/`province` `null` |
| `CardNumber` | all-same-digit card (`0000…`) | invalid `CARD_NUMBER.INVALID_CHECKSUM` | invalid `CARD_NUMBER.ALL_SAME_DIGITS` |

If you treated `isValid()` as "fully resolved", switch those call sites to `isStrictlyValid()`. If you matched `INVALID_FORMAT` to detect unknown area codes, or `INVALID_CHECKSUM` to detect all-same-digit cards, match the new codes instead.

`PhoneNumber` also stops accepting 11-digit numbers that start with `00` as landlines; they now return `INVALID_FORMAT`.

### 3. Exceptions

| Where | 0.6 | 0.7 |
|---|---|---|
| `::fake()` with a malformed pin (`NationalId`, `CardNumber`, `Iban`, `PhoneNumber`, `PlateNumber`) | `\InvalidArgumentException` | `ValidationException` with `ErrorCode::FAKE_INVALID_ARGUMENT` |
| `Money\Amount` (negative, overflow) | `FormatException` | `Exception\MoneyException` (same `AMOUNT_*` codes) |
| `ValidationException::fromResult()` on a result with no `ErrorCode` | `\LogicException` | exception with `ErrorCode::VALIDATION_FAILED` |

Code that catches `AbzarException` needs no change. Update any `catch (FormatException)` around `Amount` and any `catch (\InvalidArgumentException)` around `fake()`.

### 4. Signature changes

- `OrdinalNumber::toShort(int $n, bool $persianDigits = true, string $suffix = 'ام')`. The second argument was the string `'persian'` / `'english'`:

  ```php
  OrdinalNumber::toShort(43, 'english', 'rd');  // 0.6
  OrdinalNumber::toShort(43, false, 'rd');      // 0.7
  OrdinalNumber::toShort(43, persianDigits: false);
  ```

- `PhoneNumber::fake()` gains `PhoneNumberType $type = MOBILE` and `?string $areaCode = null`. Existing calls are unaffected.
- `PhoneNumberDetails::landline()` now accepts `?string` for `$city` and `$province`.

### 5. Behaviour changes in formatters and text tools

- `OrdinalNumber::toWord(30)` / `addSuffix('سی')` return `سی‌ام` (joined with ZWNJ) instead of `سی اُم`.
- `KeyboardFixer::enToFa()` maps upper-case letters through the ISIRI 9147 Shift layer instead of lower-casing them. For example `H` gives `آ` and `C` gives `ژ`, so `'SGHL'` no longer becomes `'سلام'`. `faToEn()` reverses the Shift layer too. If you relied on caps-lock tolerance, lower-case the input first: `KeyboardFixer::enToFa(strtolower($typed))`.
- `BillId` rejects bill IDs longer than 13 digits with `BILL_ID.WRONG_LENGTH` (the old limit was 18). Payment IDs still accept up to 18 digits.
- `Bank::AYANDEH->isDefunct()` is `true`: Ayandeh was dissolved into Bank Melli on 2025-10-23.

### 6. Bug fixes that change output

These are fixes, but they change values you may have stored or asserted on:

- **Plate data was rebuilt.** Many city codes now resolve to a different province (e.g. `77` is Tehran, not Khuzestan), and letter categories changed (e.g. `الف` is `GOVERNMENT`, not `PRIVATE`). Codes shared across a province split give a joined `province` string such as `تهران - البرز`; use the new `provinces()` list instead of parsing it. `PlateType::GOVERNMENT_CIV` and `RENTAL` are no longer produced and are deprecated.
- **Alborz is a province.** Area code `026` and the national-ID prefixes for Karaj, Savojbolagh, Taleghan and Nazarabad now resolve to `البرز` / `Province::ALBORZ` instead of Tehran.
- **Spelling and bank-name fixes.** `کهکیلویه و بویراحمد` is now spelled `کهگیلویه و بویراحمد` (the old spelling still resolves through `Province::fromPersian()`). IBAN bank code `070` now reads `بانک رسالت` and resolves to `Bank::RESALAT`.
- **Slugs** drop Persian punctuation, kashida and tashkeel, and turn ZWNJ into `-` (`می‌خواهم` → `می-خواهم`; it used to keep the ZWNJ). Regenerated slugs may not match stored ones.
- **`DigitConverter::convertContent()` / `CharNormalizer::normalizeContent()`** leave character references and `<pre>` / `<code>` / `<textarea>` content alone, and throw `FormatException` (`HTML.SEGMENTATION_FAILED`) on a PCRE failure instead of silently returning the input.
- **`WordsToNumber::parse()`** returns `null` for sequences that aren't numbers (`دو سه`) and for values past `PHP_INT_MAX`; it used to sum them or throw a `TypeError`.
- **Pasted input** with NBSP, bidi marks or Unicode dashes now validates, and `Iban` / `LegalId` accept dash-grouped input.

### 7. Additions you may want

- `extractAll()` on `PhoneNumber`, `Iban`, `PostalCode` and `PlateNumber`.
- `masked()` on `PhoneNumber` (`0912 *** 4567`) and `Iban` (`IR82 054* **** **** **** **90 02`).
- `PlateNumber::value()`, `provinces()`, `provinceEnum()` and `provinceEnums()`.
- `Currency::format()` accepts a `Money\Amount`.
- `BillId::fake(?BillType)` and `BillId::fakePaymentId(string $billId)`.
- `PhoneNumber::fake(type: PhoneNumberType::LANDLINE, areaCode: '021')`.

## 0.5 → 0.6

Namespace moves only. Behaviour, constructors and method signatures are unchanged, so update your `use` statements:

| 0.5 | 0.6 |
|---|---|
| `Eram\Abzar\Format\Currency` | `Eram\Abzar\Money\Currency` |
| `Eram\Abzar\Format\CurrencyUnit` | `Eram\Abzar\Money\Unit` |
| `Eram\Abzar\AbzarException` | `Eram\Abzar\Exception\AbzarException` |
| `Eram\Abzar\AbzarFormatException` | `Eram\Abzar\Exception\FormatException` |
| `Eram\Abzar\AbzarValidationException` | `Eram\Abzar\Exception\ValidationException` |
| `Eram\Abzar\AbzarEnvironmentException` | `Eram\Abzar\Exception\EnvironmentException` |

`composer.json` no longer has a top-level `"version"` field. The version comes from Git tags only, so tooling that read it from `composer.json` should use the installed package version instead.

New in 0.6: `Money\Amount`. See the [CHANGELOG](CHANGELOG.md).
