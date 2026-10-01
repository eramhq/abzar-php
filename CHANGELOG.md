# Changelog

All notable changes to this project are documented in this file. The format is loosely based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html) once it leaves `0.x`.

## [Unreleased]

### Added

- `WordsToNumber::parse()` reads the alternate spellings `شیش` (6), `چارصد` (400), `بیلیون` (10⁹) and `کوآدریلیون` (10¹⁵), so it now parses every cardinal persian-tools' `numberToWords()` writes.
- Reference pages for every validator: [National ID](docs/en/national-id.md), [Legal ID](docs/en/legal-id.md), [Card Number](docs/en/card-number.md), [IBAN](docs/en/iban.md), [Phone Number](docs/en/phone-number.md) and [Plate Number](docs/en/plate-number.md), alongside the existing postal-code and bill-ID pages.
- [docs/en/error-codes.md](docs/en/error-codes.md): every `ErrorCode` with its Persian message, emitting class and kind (error, warning or thrown). `ErrorCodeDocsTest` fails when a case is missing or its message is out of date.
- Contract tests for legal IDs, number words, formatting and text (`LegalIdContractTest`, `NumberWordsContractTest`, `FormattingContractTest`, `TextContractTest`), with a `divergences()` registry in each that pins every deliberate difference from persian-tools. [docs/en/persian-tools-parity.md](docs/en/persian-tools-parity.md) lists them.

### Fixed

- `HalfSpaceFixer` binds a suffix followed by a closing bracket, quote or colon: `(آبی تر)` → `(آبی‌تر)`. Before, only whitespace, the end of the text and `.,;!?؟،؛` ended a suffix.

### Changed

- `Currency::convert()` drops a redundant integer branch. Output is unchanged.
- Mutation testing ignores the random `::fake()` generators, and the MSI floor is raised to 88% (was 80) in both `composer mutate` and CI.

## [0.7.0-beta] — 2026-10-01

Correctness fixes across the lookup data, input cleaning, formatters and HTML handling, plus a consistency pass that makes every validator follow the same rules. **Contains breaking changes:** see [UPGRADE.md](UPGRADE.md) for migration snippets.

### Changed (breaking — 0.x)

- **One warning rule for every validator.** Well-formed, checksum-valid input is valid; an unknown lookup adds a warning code and leaves the lookup field `null`:
  - `NationalId`: unknown city prefix → `NATIONAL_ID.UNKNOWN_CITY_CODE` warning (was silently valid).
  - `Iban`: unknown bank code → `IBAN.UNKNOWN_BANK` warning (was silently valid).
  - `PhoneNumber`: landline with an uncatalogued `0[1-8]x` area code → valid with `PHONE_NUMBER.UNKNOWN_AREA_CODE` and null city / province (was invalid `PHONE_NUMBER.INVALID_FORMAT`). 11-digit input starting `00` is no longer read as a landline.
  - `CardNumber`: all-same-digit cards → `CARD_NUMBER.ALL_SAME_DIGITS` (was `CARD_NUMBER.INVALID_CHECKSUM`).
- **`from()` / `tryFrom()` / `extractAll()` accept warning-bearing results** on `CardNumber`, `PhoneNumber` and `PlateNumber`, matching the other validators. This reverses the 0.5 strict-VO rule. `isStrictlyValid()` is unchanged; use it for strict acceptance.
- `::fake()` throws `ValidationException` with `ErrorCode::FAKE_INVALID_ARGUMENT` instead of `\InvalidArgumentException`.
- `Money\Amount` throws the new `Exception\MoneyException` instead of `FormatException` (same `AMOUNT_*` codes).
- `ValidationException::fromResult()` falls back to `ErrorCode::VALIDATION_FAILED` instead of throwing `\LogicException` when the result has no code.
- `OrdinalNumber::toShort(int $n, bool $persianDigits = true, string $suffix = 'ام')` — the second argument was the string `'persian'` / `'english'`.
- `OrdinalNumber` joins `ام` to words ending in `ی` with ZWNJ: `سی‌ام` (was `سی اُم`). Deliberate divergence from persian-tools.
- `KeyboardFixer::enToFa()` maps upper-case letters through the ISIRI 9147 Shift layer (`H` → `آ`, `C` → `ژ`, `M` → `ء`, `B` → ZWNJ, …) instead of lower-casing them; `faToEn()` reverses it.
- `BillId` caps bill IDs at 13 digits (was 18); the `BILL_ID.WRONG_LENGTH` message now states the range.
- `Bank::AYANDEH->isDefunct()` is `true`: Ayandeh was dissolved into Bank Melli on 2025-10-23.

### Added

- **`Amount` arithmetic and comparison API** (shipped on `main` after 0.6.0-beta, previously unlisted): `times(int $qty)`, `percentOf(int|float $pct, int $mode = PHP_ROUND_HALF_EVEN)` (banker's rounding by default), `greaterThanOrEqual()`, `lessThanOrEqual()`, `compareTo()` (`usort`-ready). `fromToman()`, `add()` and `times()` trap `PHP_INT_MAX` overflow with the new `ErrorCode::AMOUNT_OVERFLOW`; `fromToman()` also rejects negatives explicitly instead of leaking a `TypeError` for `PHP_INT_MIN`.
- `Province::ALBORZ` (`البرز`).
- `PlateType::TEMPORARY` (`گ` — گذر موقت).
- `PlateNumberDetails::$provinces` (`list<string>`, also in `jsonSerialize()` as `provinces`) — every province a plate city code was issued in. Codes issued before a province split list each successor (e.g. `21` → `['تهران', 'البرز']`); `$province` then holds the names joined with ` - `.
- `DataSources::plateCodes()` / `DataSources::plateLetters()` — the plate tables now live in `src/Data/PlateCodes.php` / `src/Data/PlateLetters.php` like every other lookup table.
- `ErrorCode::HTML_SEGMENTATION_FAILED` — raised (as `FormatException`) by `HtmlSegmenter::transformText()` and therefore `DigitConverter::convertContent()` / `CharNormalizer::normalizeContent()` when PCRE cannot segment the input.
- CI: PHP 8.5 in the test and release matrices, `composer validate --strict`, read-only default token permissions, a per-ref concurrency group, a composer cache keyed on `composer.lock`, and a PHP 8.1 job without `ext-intl`.
- `extractAll()` on `PhoneNumber`, `Iban`, `PostalCode` and `PlateNumber`, sharing one internal engine with `NationalId::extractAll()` / `CardNumber::extractAll()`.
- `masked()` on `PhoneNumber` (`0912 *** 4567`, `021 **** 7777`) and `Iban` (`IR82 054* **** **** **** **90 02`).
- `PlateNumber::value()`, `provinces()`, `provinceEnum()` (single-province codes only) and `provinceEnums()`; `PlateNumberDetails::provinceEnum()` / `provinceEnums()`.
- `Currency::format()` accepts a `Money\Amount`, rendered in the requested unit without truncating sub-toman rials.
- `BillId::fake(?BillType $type = null)` and `BillId::fakePaymentId(string $billId)`.
- `PhoneNumber::fake()` can generate landlines: `fake(type: PhoneNumberType::LANDLINE, areaCode: '021')`.
- `Exception\MoneyException`; `ValidationException::forFakeArgument()`.
- `ErrorCode` cases: `NATIONAL_ID_UNKNOWN_CITY_CODE`, `CARD_NUMBER_ALL_SAME_DIGITS`, `IBAN_UNKNOWN_BANK`, `PHONE_NUMBER_UNKNOWN_AREA_CODE`, `VALIDATION_FAILED`, `FAKE_INVALID_ARGUMENT`.
- `UPGRADE.md`.

### Fixed

- **Plate city codes** rebuilt from the persian-tools numberplate dataset, cross-checked against fa.wikipedia. The old table was wrong for most codes (e.g. `77` resolved to Khuzestan; it is Tehran) and missed dozens of real ones. 88 codes are now mapped (was 47). Deviations from upstream are documented in the data file header.
- **Plate letter categories** corrected: `الف` is government (was private), `پ` police, `ث` / `ز` / `ش` / `ف` military, `ژ` disabled, `ع` public transport, `گ` temporary, `ط` / `م` private. `PlateType::GOVERNMENT_CIV` and `PlateType::RENTAL` no longer have a letter mapped and are `@deprecated`.
- Plates typed with Arabic `ي` / `ك` are folded to Persian `ی` / `ک` before the letter lookup instead of being reported as an unknown letter.
- `Iban` results for bank code `070` (Resalat) now resolve via `bankEnum()` to `Bank::RESALAT`; the table name was a spelling `Bank::fromPersian()` didn't know. `بانک قرض الحسنه رسالت` is also accepted as an alias.
- Province name `کهگیلویه و بویراحمد` corrected (was `کهکیلویه…`) in every table; the old spelling still resolves through `Province::fromPersian()`.
- Area code `026` and national-ID prefixes for Karaj, Savojbolagh, Taleghan and Nazarabad now resolve to Alborz instead of Tehran.
- **Pasted input**: every validator now strips NBSP / narrow NBSP, ZWNJ / ZWJ, LRM / RLM / ALM, bidi embeddings and isolates, BOM, soft hyphen, and Unicode dashes / minus (`U+2010`–`U+2015`, `U+2212`), so IDs copied from phones and RTL chat apps validate. `Iban` and `LegalId` now share this input cleaning (and accept dash-grouped input).
- `NumberFormatter::withSeparators()` accepts its own sibling's output: `،` / `٬` grouping, the Arabic decimal separator `٫`, a leading `+`, and space / NBSP grouping — so `withSeparators(Currency::format($n, withUnit: false))` round-trips.
- `WordsToNumber::parse()` returns `null` instead of throwing `TypeError` past `PHP_INT_MAX` (e.g. `ده کوینتیلیون`), and rejects sequences that don't form a number (`دو سه`, `بیست سی`, `یک میلیون دو میلیون`) instead of summing them. Colloquial split hundreds (`سه صد`, `یک صد`) now parse as 300 / 100 (was 103 / 101).
- `DigitConverter::convertContent()` / `CharNormalizer::normalizeContent()` no longer corrupt character references (`&#8204;` became `&#۸۲۰۴;`) and leave `<pre>`, `<code>`, `<textarea>` content alone alongside `<script>` / `<style>`. A literal `<` in text no longer disables the transform for the rest of that segment. PCRE failures raise `FormatException` instead of silently returning the input unconverted.
- `Slug::generate()` strips Persian punctuation (`،` `؛` `؟` `٪` `٫` `٬` `۔`), kashida and tashkeel; ZWNJ becomes a `-` separator instead of being kept inside the slug.

### Docs and packaging

- `docs/en/currency.md`: `Amount` example values were 10× too small.
- Companion package is `eram/daynum` on Packagist (was `eramhq/daynum`, a 404) in `composer.json` `suggest` and the docs.
- Version pins in README / installation / API-stability docs bumped to `^0.7@beta`.
- README: Money section covers `times` / `percentOf` / `compareTo`; removed the "byte-identical messages" claim that contradicted the API-stability policy.
- CONTRIBUTING: current `ValidationResult` factories, `src/Data/` table location, input-cleaning helper.
- `docs/en/async-runtimes.md` lists every process-wide static cache.
- `.gitattributes` export-ignores `composer.lock`, `infection.json5`, `phpbench.json`, `.php-cs-fixer.dist.php`, `.editorconfig`, `.gitignore` and `.gitattributes`; `.gitignore` adds `var/` and `.phpbench/`.
- `ErrorCodeMessageSnapshotTest` covers every `ErrorCode` case and fails when a new case lacks a snapshot.
- README "`isValid()` vs `isStrictlyValid()`" rewritten for the uniform rule; examples for the new extractors, masks, plate provinces, `Currency::format(Amount)` and the fake helpers.
- `docs/en/words-to-number.md` no longer claims large values overflow to `float`; it documents the ordering rules and `null` on overflow.
- `docs/en/keyboard-fixer.md`, `bill-id.md`, `currency.md` and `api-stability.md` updated for the changes above.

## [0.6.0-beta] — 2026-04-18

### Added

- **`Eram\Abzar\Money\Amount`** — immutable value object for Iranian currency amounts. Stored internally as Rials to eliminate the Rial/Toman ×10 confusion. Factories `::fromRials()` / `::fromToman()`; accessors `->inRials()` / `->inToman()`; arithmetic `->add()` / `->subtract()`; comparisons `->equals()` / `->greaterThan()` / `->lessThan()` / `->isZero()`; `JsonSerializable` emits `{"rials": N}`. Deliberately not `Stringable` — callers must pick `inRials()` / `inToman()` / `Money\Currency::format()` so the unit is always explicit. Negative construction throws `FormatException` with `ErrorCode::AMOUNT_NEGATIVE`. Pair with `Money\Currency` for display formatting. Ported from `eram/pardakht` and `eram/ersal` to end their duplicated copies.
- `ErrorCode::AMOUNT_NEGATIVE` — emitted when an `Amount` is constructed with a negative rial value.

### Changed

- **BC break:** moved `Eram\Abzar\Format\Currency` → `Eram\Abzar\Money\Currency` and renamed `Eram\Abzar\Format\CurrencyUnit` → `Eram\Abzar\Money\Unit`. Centralises the Rial/Toman domain (formatter, enum, `Amount` VO) under a single `Money\` namespace. Update `use` statements; public method signatures are unchanged.
- **BC break:** exceptions moved from top-level `Eram\Abzar\` to `Eram\Abzar\Exception\`, and the concrete subclasses lost their `Abzar` prefix:
  - `Eram\Abzar\AbzarException` → `Eram\Abzar\Exception\AbzarException` (base kept the prefix to avoid shadowing PHP's `\Exception`)
  - `Eram\Abzar\AbzarFormatException` → `Eram\Abzar\Exception\FormatException`
  - `Eram\Abzar\AbzarValidationException` → `Eram\Abzar\Exception\ValidationException`
  - `Eram\Abzar\AbzarEnvironmentException` → `Eram\Abzar\Exception\EnvironmentException`
  - Behaviour, constructors, and static factories are unchanged — only `use` statements and class references.

## [0.5.0-beta] — 2026-04-17

First tagged release of the 0.4 / 0.5 line. Folds the untagged `[0.3.1-beta]` entries in as well — their fixes were never shipped standalone and reach consumers for the first time here.

### Added

- **Value-object API.** All seven validators (`NationalId`, `CardNumber`, `Iban`, `LegalId`, `PhoneNumber`, `PostalCode`, `BillId`) expose `::from($input): static` and `::tryFrom($input): ?static` alongside the existing `::validate(): ValidationResult`. Instances are `Stringable` + `JsonSerializable` with typed accessors (e.g. `$ni->city()`, `$card->bin()`, `$phone->isMobile()`).
- Display formatters on the three long-numeric value objects so UIs don't have to roll their own:
  - `CardNumber::formatted()` → `6037 9912 3456 7893` (4-4-4-4 grouped).
  - `CardNumber::masked()` → `6037 99** **** 7893` (PCI first-6 / last-4).
  - `PhoneNumber::formatted(bool $international = false)` → `0912 123 4567` / `+98 912 123 4567` (mobile), `021 8888 7777` / `+98 21 8888 7777` (landline — leading `0` of the area code dropped in intl form).
  - `Iban::formatted()` → `IR82 0540 1026 8002 0817 9090 02` (4-char groups + 2-char tail).
- `::fake()` factories across every validator for fixtures and tests (named `fake` — not `generate` — to discourage production use):
  - `NationalId::fake(?string $cityCode = null): string`, `CardNumber::fake(?string $bin = null): string`, `LegalId::fake(): string`.
  - `PhoneNumber::fake(?string $operatorPrefix = null): string` — valid mobile; optional 3-digit operator-prefix pin.
  - `Iban::fake(?string $bankCode = null): string` — valid `IR…` IBAN with ISO 13616 mod-97 check digits; optional 3-digit bank-code pin.
  - `PostalCode::fake(): string` — 10-digit code that satisfies all validator pattern rules (first/fifth ≠ 0, no 4-run).
  - `PlateNumber::fake(?PlateType $type = null): string` — canonical `NN[letter]NNN-NN`; passing a `PlateType` returns a letter mapped to that category. `PlateType::OTHER` throws (it represents unknown letters, not a real category).
- `NationalId::extractAll(string $text): list<NationalId>`, `CardNumber::extractAll(string $text): list<CardNumber>` — free-text extractors that pull out 10- or 16-digit runs and filter by validator.
- `Eram\Abzar\Validation\PlateNumber` + `PlateNumberDetails` + `PlateType` — Iranian license plate parser (`NN[letter]NNN-NN`) with letter-derived type category and city-code → province lookup.
- `Eram\Abzar\Text\PersianCollator` — thin `\Collator('fa_IR')` wrapper with `sort` / `sortBy` helpers. Requires `ext-intl`; throws `EnvironmentException` when missing.
- `Eram\Abzar\Text\HalfSpaceFixer` — best-effort zero-width non-joiner placement for common Persian affixes (`می`, `نمی`, `ها`, `تر`, `ترین`, `ام`, `ای`, `اید`, `اند`, …).
- `OrdinalNumber::toShort()` accepts a third `$suffix` parameter (default `ام`), so callers asking for English digits can opt into an English suffix instead of hybrid-script output like `43ام`.
- `BillId::validatePair(string $billId, string $paymentId): ValidationResult` — pair-validation with cross-checksum. `BillId::validate()` is now single-field and returns details with `paymentId = null`.
- `ValidationResult::isStrictlyValid(): bool` — `true` when the result is valid AND carries no warnings; the shared guard used by VO constructors. Distinguishes "can yield a VO" from the looser "passed validation" of `isValid()`.
- `ValidationDetail` marker interface (`Eram\Abzar\Validation\Details\ValidationDetail`) implemented by every `*Details` DTO. `ValidationResult::detail()` now returns `?ValidationDetail` instead of `?\JsonSerializable`, so callers can narrow without unrelated `@var` annotations.
- `LegalIdDetails` DTO (previously `LegalId` was the sole validator returning a bare string). `LegalId::validate()` now emits it through `ValidationResult::valid()` for symmetry with every other validator.
- `EnvironmentException` — new concrete `AbzarException` subclass for runtime-prerequisite failures (e.g. `ext-intl` missing when opting into NFC normalization). Carries `ErrorCode::ENV_MISSING_EXT_INTL`.
- `Eram\Abzar\Validation\BillType` — backed enum replacing the `BillId::TYPES` string map. Cases: `WATER`, `ELECTRIC`, `GAS`, `PHONE`, `MOBILE`, `TAX`, `SERVICES`, `PASSPORT`, `OTHER`. `BillIdDetails::$type` now holds this enum directly; `BillId::type()` returns `BillType`.
- `Eram\Abzar\Validation\PhoneNumberType` — backed enum (`MOBILE` / `LANDLINE`) replacing raw strings on `PhoneNumberDetails::$type` / `PhoneNumber::type()`.
- `PhoneNumberDetails::mobile()` / `::landline()` named constructors — the direct constructor is private; the two factories make mobile/landline variants structurally unambiguous.
- Canonical input now carried on each detail DTO (`NationalIdDetails::$value`, `CardNumberDetails::$value`, `IbanDetails::$value`). Value objects read through to the DTO rather than storing a redundant second copy.
- `PersianNumerals::SCALES` extended with `کوینتیلیون` (10¹⁸), covering the full `int` range up to `PHP_INT_MAX`. `WordsToNumber` lookup updated in lockstep.

### Changed (breaking — 0.x)

- **`ValidationResult` factories renamed** to eliminate the old positional-details-vs-warnings ambiguity:
  - `ValidationResult::success($details, $warnings)` → `ValidationResult::valid($detail?)` + `ValidationResult::validWithWarnings($warnings, $detail?)`.
  - `ValidationResult::failure($errors, …)` → `ValidationResult::invalid($errors, $warnings?)`.
- **Typed detail DTOs** replace `array<string, mixed>`. `ValidationResult::details(): array` is gone; `ValidationResult::detail(): ?ValidationDetail` returns a per-validator `readonly` DTO under `Eram\Abzar\Validation\Details\` (`NationalIdDetails`, `CardNumberDetails`, `IbanDetails`, `PhoneNumberDetails`, `BillIdDetails`, `PostalCodeDetails`, `PlateNumberDetails`, `LegalIdDetails`). `ValidationResult::bank() / operator() / province()` helpers were removed — access them via the value object (e.g. `$card->bankEnum()`) or DTO property.
- **Single exception hierarchy.** Every library-raised exception now extends the new abstract `Eram\Abzar\Exception\AbzarException`, which carries an `errorCode(): ErrorCode`. Formatters throw `FormatException` (previously plain `\InvalidArgumentException`); VO constructors throw `ValidationException` (new; also exposes the originating `ValidationResult`). `catch (AbzarException $e)` now covers every failure uniformly.
- **Result-vs-throw policy documented.** `docs/en/api-stability.md` gained a "Result-vs-throw policy" section. Short version: validators return a result, formatters fail fast.
- Value-object entry points (`::from()`, `::tryFrom()`, `::extractAll()`) reject warning-bearing results across `CardNumber`, `PhoneNumber`, and `PlateNumber` (via the new `ValidationResult::isStrictlyValid()` guard). `validate()` still accepts these with warnings (unchanged scope decision) — the rejection applies only to APIs that return a VO, where the warning would otherwise be invisible past the VO boundary. Holding a `CardNumber` VO now guarantees `bank() !== null`; a `PhoneNumber` mobile VO guarantees `operator() !== null`; a `PlateNumber` VO guarantees `province() !== null` and `type() !== PlateType::OTHER`. `ValidationException::fromResult()` accepts warning-only results and carries the warning's `ErrorCode`.
- `ValidationResult::__toString()` now surfaces warnings on valid-with-warnings results (previously always returned the literal `'valid'`, which was useless); unchanged for `valid()` (still `'valid'`) and for rejections (still joined error messages).
- `CardNumber::validate()` no longer rejects Luhn-valid card numbers whose 6-digit BIN isn't in the bundled bank table. They pass with `bank: null` and a `CARD_NUMBER_UNKNOWN_BIN` warning — matching the existing `Iban` behaviour for unknown `bankCode`. All-zero (`0000000000000000`) is still rejected as a degenerate Luhn pass. `CardNumberDetails::$bank` and `CardNumber::bank()` are now `?string`.
- `PhoneNumber::validate()` accepts mobile numbers whose `09xx` prefix isn't in the operator table — returns a valid result with `operator: null` and `PHONE_NUMBER_UNKNOWN_OPERATOR` warning. Covers MVNOs that aren't yet catalogued. Divergence from persian-tools is documented in the contract test.
- `PhoneNumber::validate()` auto-prepends `0` to 10-digit landline inputs when the first two digits match a known area code (`2112345678` → `02112345678`) — CSV / Excel round-trips commonly drop the leading zero.
- `PhoneNumber::validate()` also tolerates `.` in input (e.g. `+98.9121234567`) alongside the pre-existing spaces / dashes / parens handling.
- `PhoneNumberDetails::mobile()` third parameter `$operator` is now `?string`.
- `NationalId::validate()` no longer silently left-pads 8- or 9-digit input to 10. It rejects with the new `ErrorCode::NATIONAL_ID_LIKELY_TRUNCATED` and an error message that points callers at `str_pad($input, 10, '0', STR_PAD_LEFT)`. Silent padding was hiding upstream `intval` / CSV bugs.
- `BillId::validate(string, string)` split into `BillId::validate(string $billId)` (single-field) and `BillId::validatePair(string $billId, string $paymentId)`. `BillIdDetails::$paymentId` is now `?string`.
- `CharNormalizer::$normalizeToNfc` without `ext-intl` throws `EnvironmentException` (rather than `\LogicException`), keeping the single-root-exception contract. Caught by `catch (AbzarException $e)`.
- `CharNormalizer::$stripBidiMarks` now also strips U+200D (ZWJ) and U+FEFF (BOM) — both travel in alongside bidi control characters when text is copied from Word / Office.

### Fixed

- `NumberToWords::convert()` no longer silently truncates floats with magnitude above `PHP_INT_MAX`. The float path now throws `FormatException` with `ErrorCode::NUMBER_TO_WORDS_OUT_OF_RANGE`. The prior raw `\OverflowException` thrown from the scale-exhaustion branch is now the same `FormatException`, honoring the `catch (AbzarException $e)` contract.
- `NumberToWords::convert()` now throws `FormatException` with `ErrorCode::NUMBER_TO_WORDS_PRECISION_LOSS` when a float carries more than `PHP_FLOAT_DIG` significant digits. IEEE-754 has already rounded at that point — we'd otherwise return a plausibly-wrong word.
- `NumberToWords::convert()` now preserves leading zeros in the fractional part. `3.05` renders as `سه ممیز صفر پنج` (previously collapsed to `سه ممیز پنج`). Output is a **behavioural break** relative to 0.3.0-beta — acceptable under the documented `0.x` stability policy.
- `WordsToNumber::parse()` accepts leading `صفر` tokens after `ممیز` and counts them as zero-padding, so round-tripping `3.05` through `NumberToWords` → `WordsToNumber` now yields `3.05` exactly. Previously these inputs returned `null`.
- `ErrorCode::PHONE_NUMBER_INVALID_FORMAT` message no longer claims mobile-only. `PhoneNumber::validate()` has accepted landlines since 0.3.0-beta; the message is now `شماره تلفن باید یک شماره موبایل یا تلفن ثابت ایرانی معتبر باشد`. Error code value (`PHONE_NUMBER.INVALID_FORMAT`) is unchanged.
- `BillId::validatePair()` no longer mislabels `payment_id` input errors as `bill_id` errors. Empty / short `payment_id` inputs now emit `ErrorCode::BILL_ID_PAYMENT_EMPTY` / `BILL_ID_PAYMENT_WRONG_LENGTH` with Persian messages that say `شناسه پرداخت` rather than `شناسه قبض`. **Breaking** for consumers asserting on the old `BILL_ID_EMPTY` / `BILL_ID_WRONG_LENGTH` codes for `payment_id` failures.

### Removed

- `ValidationResult::success()`, `ValidationResult::failure()`, and `ValidationResult::details()` — superseded by the named factories and typed DTO accessor above.
- `ValidationResult::bank()`, `::operator()`, `::province()` shortcut accessors — fetch from the value object or detail DTO instead.
- Private `NationalId::canonicalize()` and `Iban::canonicalize()` — the canonical string now lives on the detail DTO, so no second normalization pass is needed on the `::from()` happy path.

### Docs

- Version pin across `README.md`, `docs/en/installation.md`, and `docs/en/api-stability.md` updated to `^0.5@beta`.
- README feature matrix and examples updated for `PlateNumber`, `PersianCollator`, `HalfSpaceFixer`, and the new `fake` / `extractAll` helpers.
- `docs/en/related.md` no longer claims persian-tools parity tests are planned (they shipped in 0.3.0-beta) and no longer hedges the structured-error-codes row.

### Error-code additions

`ErrorCode::NATIONAL_ID_LIKELY_TRUNCATED`, `CARD_NUMBER_UNKNOWN_BIN`, `PHONE_NUMBER_UNKNOWN_OPERATOR`, `PLATE_NUMBER_EMPTY`, `PLATE_NUMBER_INVALID_FORMAT`, `PLATE_NUMBER_UNKNOWN_LETTER`, `PLATE_NUMBER_UNKNOWN_CITY_CODE`, `NUMBER_TO_WORDS_PRECISION_LOSS`, `ENV_MISSING_EXT_INTL`, `BILL_ID_PAYMENT_EMPTY`, `BILL_ID_PAYMENT_WRONG_LENGTH`.

## [0.3.0-beta] — 2026-04-16

First tagged release. Supersedes the untagged `[0.1.0-beta]` draft that never reached a git tag.

### Added

- **Stable error codes.** `Eram\Abzar\Validation\ErrorCode` — backed enum with `DOMAIN.REASON` values for every validator and format-exception failure. Renames are breaking; new cases are additive.
- `ValidationResult` grew paired typed accessors: `errorCodes(): list<ErrorCode>`, `warnings(): list<string>`, `warningCodes(): list<ErrorCode>`, and convenience lookups `bank(): ?Bank`, `operator(): ?Operator`, `province(): ?Province`.
- `ValidationResult::success()` accepts an optional `warnings` argument; `ValidationResult::failure()` accepts `string|ErrorCode|list<string|ErrorCode>`.
- `ValidationResult` implements `JsonSerializable` and `Stringable`; `jsonSerialize()` emits `{valid, errors, error_codes, warnings?, warning_codes?, details}` and `__toString()` joins Persian error messages with `; `.
- `declare(strict_types=1)` on every source file — silent numeric coercion is now a type error.
- All static-only classes are `final` with a `private __construct()` — they can no longer be instantiated or subclassed.
- `Eram\Abzar\Validation\Bank` — 37-case enum with canonical bank names. Card-surface aliases (e.g. `موسسه کوثر` → `KOSAR`) resolve via `Bank::fromPersian()`. `isDefunct()` flags merged institutions.
- `Eram\Abzar\Validation\Operator` — 6-case enum for mobile-operator lookup.
- `Eram\Abzar\Validation\Province` — 31-case enum with Arabic-Yeh / Arabic-Kaf tolerant lookup.
- `Eram\Abzar\Validation\PostalCode` — 10-digit Iranian postal code validator.
- `Eram\Abzar\Validation\BillId` — `شناسه قبض` / `شناسه پرداخت` mod-11 validator with bill-type decoding. Algorithm verified against [persian-tools@25a2dc9](https://github.com/persian-tools/persian-tools/blob/25a2dc9f22444b78bf16f6c48bda6727688e8552/src/modules/bill/index.ts).
- `Eram\Abzar\Text\KeyboardFixer` — swap between English QWERTY and Persian keyboard layouts.
- `Eram\Abzar\Format\WordsToNumber` — parse Persian number words back to `int|float|null`. Shares the `PersianNumerals` table with `NumberToWords`.
- `Eram\Abzar\Format\Currency` + `CurrencyUnit` — Toman / Rial formatter and converter. _(Moved to `Eram\Abzar\Money\Currency` + `Money\Unit` in 0.6.)_
- `CharNormalizer` opt-in flags (all default `false`): `foldHamza`, `stripTashkeel`, `stripKashida`, `stripBidiMarks`, `normalizeToNfc` (requires `ext-intl`).
- `Eram\Abzar\Text\HtmlSegmenter` — internal helper that splits HTML into tag vs. text segments, shared by `CharNormalizer::normalizeContent()` and `DigitConverter::convertContent()`. HTML comments are now uniformly preserved by both.
- `Slug::generate()` accepts an optional `CharNormalizer` argument, so callers can pass a custom-configured normalizer (e.g. `tehMarbuta: true`) without hitting a shared default-config cache.
- `Eram\Abzar\Data\DataSources` + extracted data files (`NationalIdCityCodes`, `CardBanks`, `IbanBanks`, `PhoneOperators`, `PhoneAreaCodes`). Each validator lazy-loads its table on first call.
- `TimeAgo::format()` gained an optional `jalaliMonthResolver` callback, invoked only when the diff lands in the `سال` bucket. No hard `eramhq/daynum` dependency.
- Persian-tools contract parity tests (`tests/Unit/Fixtures/PersianToolsContractTest.php`) — ~40 vectors lifted from upstream specs covering NationalId, CardNumber, Iban, PhoneNumber, Province, and BillId. Pulled via `composer fixtures:pull` from a pinned SHA (`tools/fixtures/SHA`); fixture tree is vendored under `tests/fixtures/persian-tools/` and `export-ignore`'d from the Packagist archive via `/tests`.
- Benchmark scaffolding (`phpbench/phpbench` dev dep, `tools/benchmarks/*Bench.php`, `composer bench`). Advisory `bench` job in CI uploads phpbench JSON as an artifact.
- Mutation-testing scaffolding (`infection/infection` dev dep, `infection.json5`, `composer mutate`). Advisory `mutate` job in CI pinned to PHP 8.2. MSI floor pending CI baseline — `infection.json5` ships `minMsi: 80` as the aspirational target, but the `--min-msi=0` flag on the CI run measures rather than enforces until a floor is recorded.
- `composer suggest` for [`eramhq/daynum`](https://github.com/eramhq/daynum) (jalali / shamsi calendar) and `ext-intl` (only for the `normalizeToNfc` flag).
- `SECURITY.md`, GitHub issue / PR templates, Dependabot configuration.
- `friendsofphp/php-cs-fixer` dev dependency, `.php-cs-fixer.dist.php` config, `composer cs-check` / `composer cs-fix` scripts, and a CI style-check job.
- `docs/en/`: installation, API stability policy, async-runtime safety note, per-class references (`postal-code.md`, `bill-id.md`, `keyboard-fixer.md`, `words-to-number.md`, `currency.md`), and integration recipes for Laravel FormRequest, Symfony Validator, Symfony Console, and WordPress.
- README: "Related packages", "Versus other Persian PHP libraries" comparison table, and "Framework bridges" section pointing to the recipes.
- `composer.json` now explicitly requires `ext-mbstring`.

### Changed

- `NationalId::validate()` now accepts any 3-digit prefix that passes mod-11, returning `city = null` / `province = null` when the prefix isn't in the city-code table. Previously unknown prefixes were rejected with `NATIONAL_ID_INVALID_PREFIX`. Matches upstream persian-tools default (`checkPrefix: false`). **Breaking**: IDs like `2540201288` / `4400276201` are now accepted.
- `CardNumber::validate()` now rejects card numbers whose 6-digit BIN isn't in the Iranian bank table, even if they pass Luhn. Matches upstream; blocks `0000000000000000`, `4000000000000002` (Visa test), etc. **Breaking**: callers relying on Luhn-only acceptance for non-Iranian cards need to adjust.
- `PhoneNumber::validate()` now rejects mobile numbers whose `09xx` prefix isn't in the operator table. `09402002580`-style inputs are no longer accepted. Landline area-code validation is unchanged.
- `PhoneNumber::validate()` — previously-rejected `02112345678`-style landlines are now accepted and classified. The `details.type` field, previously always `'mobile'`, can now be `'landline'`.
- Validators now route Persian messages through `ErrorCode::message()`; consumer assertions against the pre-0.3 Persian strings remain byte-for-byte equal.
- Exception messages in `NumberFormatter::withSeparators()` and `TimeAgo::format()` now strip control characters and truncate user input before interpolation, to avoid leaking or log-injecting raw inputs.

### Removed

- `ErrorCode::NATIONAL_ID_INVALID_PREFIX` — unreachable after the prefix-policy change above.

### Notes

- Requires PHP 8.1+. No runtime Composer or PHP-extension dependencies beyond `mbstring`.
- MIT licensed. Validation data tables originate from the MIT-licensed [persian-tools](https://github.com/persian-tools/persian-tools) project (pinned at SHA `25a2dc9f` for contract tests).
- This is the first tagged release. The earlier `[0.1.0-beta]` CHANGELOG heading was never tagged; its contents are folded into this release. No downstream consumers were pinning `^0.1@beta`.
