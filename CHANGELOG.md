# Changelog

All notable changes to this project are documented in this file. The format is loosely based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html) once it leaves `0.x`.

## [Unreleased]

### Added

- Display formatters on the three long-numeric value objects so UIs don't have to roll their own:
  - `CardNumber::formatted()` → `6037 9912 3456 7893` (4-4-4-4 grouped).
  - `CardNumber::masked()` → `6037 99** **** 7893` (PCI first-6 / last-4).
  - `PhoneNumber::formatted(bool $international = false)` → `0912 123 4567` / `+98 912 123 4567` (mobile), `021 8888 7777` / `+98 21 8888 7777` (landline — leading `0` of the area code dropped in intl form).
  - `Iban::formatted()` → `IR82 0540 1026 8002 0817 9090 02` (4-char groups + 2-char tail).
- `::fake()` factories on the remaining four validators, completing the family alongside the existing `NationalId::fake()` / `CardNumber::fake()` / `LegalId::fake()`:
  - `PhoneNumber::fake(?string $operatorPrefix = null): string` — valid mobile; optional 3-digit operator-prefix pin (matches the `CardNumber::fake(?bin)` shape).
  - `Iban::fake(?string $bankCode = null): string` — valid `IR…` IBAN with ISO 13616 mod-97 check digits; optional 3-digit bank-code pin.
  - `PostalCode::fake(): string` — 10-digit code that satisfies all validator pattern rules (first/fifth ≠ 0, no 4-run).
  - `PlateNumber::fake(?PlateType $type = null): string` — canonical `NN[letter]NNN-NN`; passing a `PlateType` returns a letter mapped to that category. `PlateType::OTHER` throws (it represents unknown letters, not a real category).
- `ValidationDetail` marker interface (`Eram\Abzar\Validation\Details\ValidationDetail`) implemented by every `*Details` DTO. `ValidationResult::detail()` now returns `?ValidationDetail` instead of `?\JsonSerializable`, so callers can narrow without unrelated `@var` annotations.
- `LegalIdDetails` DTO (previously `LegalId` was the sole validator returning a bare string). `LegalId::validate()` now emits it through `ValidationResult::valid()` for symmetry with every other validator.
- `AbzarEnvironmentException` — new concrete `AbzarException` subclass for runtime-prerequisite failures (e.g. `ext-intl` missing when opting into NFC normalization). Carries `ErrorCode::ENV_MISSING_EXT_INTL`.
- `NationalId::fake(?string $cityCode = null): string`, `CardNumber::fake(?string $bin = null): string`, `LegalId::fake(): string` — valid-by-construction generators for fixtures and tests. Named `fake` (not `generate`) to discourage production use.
- `NationalId::extractAll(string $text): list<NationalId>`, `CardNumber::extractAll(string $text): list<CardNumber>` — free-text extractors that pull out 10- or 16-digit runs and filter by validator.
- `Eram\Abzar\Validation\PlateNumber` + `PlateNumberDetails` + `PlateType` — Iranian license plate parser (`NN[letter]NNN-NN`) with letter-derived type category and city-code → province lookup.
- `Eram\Abzar\Text\PersianCollator` — thin `\Collator('fa_IR')` wrapper with `sort` / `sortBy` helpers. Requires `ext-intl`; throws `AbzarEnvironmentException` when missing.
- `Eram\Abzar\Text\HalfSpaceFixer` — best-effort zero-width non-joiner placement for common Persian affixes (`می`, `نمی`, `ها`, `تر`, `ترین`, `ام`, `ای`, `اید`, `اند`, …).
- `OrdinalNumber::toShort()` accepts a third `$suffix` parameter (default `ام`), so callers asking for English digits can opt into an English suffix instead of hybrid-script output like `43ام`.
- `BillId::validatePair(string $billId, string $paymentId): ValidationResult` — pair-validation with cross-checksum. `BillId::validate()` is now single-field and returns details with `paymentId = null`.

### Changed (breaking — 0.x)

- `CardNumber::validate()` no longer rejects Luhn-valid card numbers whose 6-digit BIN isn't in the bundled bank table. They pass with `bank: null` and a `CARD_NUMBER_UNKNOWN_BIN` warning — matching the existing `Iban` behaviour for unknown `bankCode`. All-zero (`0000000000000000`) is still rejected as a degenerate Luhn pass. `CardNumberDetails::$bank` and `CardNumber::bank()` are now `?string`.
- `PhoneNumber::validate()` accepts mobile numbers whose `09xx` prefix isn't in the operator table — returns a valid result with `operator: null` and `PHONE_NUMBER_UNKNOWN_OPERATOR` warning. Covers MVNOs that aren't yet catalogued. Divergence from persian-tools is documented in the contract test.
- `PhoneNumber::validate()` auto-prepends `0` to 10-digit landline inputs when the first two digits match a known area code (`2112345678` → `02112345678`) — CSV / Excel round-trips commonly drop the leading zero.
- `PhoneNumber::validate()` also tolerates `.` in input (e.g. `+98.9121234567`) alongside the pre-existing spaces / dashes / parens handling.
- `PhoneNumberDetails::mobile()` third parameter `$operator` is now `?string`.
- `NationalId::validate()` no longer silently left-pads 8- or 9-digit input to 10. It rejects with the new `ErrorCode::NATIONAL_ID_LIKELY_TRUNCATED` and an error message that points callers at `str_pad($input, 10, '0', STR_PAD_LEFT)`. Silent padding was hiding upstream `intval` / CSV bugs.
- `BillId::validate(string, string)` split into `BillId::validate(string $billId)` (single-field) and `BillId::validatePair(string $billId, string $paymentId)`. `BillIdDetails::$paymentId` is now `?string`.
- `CharNormalizer::$normalizeToNfc` without `ext-intl` throws `AbzarEnvironmentException` (rather than `\LogicException`), keeping the single-root-exception contract. Caught by `catch (AbzarException $e)`.
- `CharNormalizer::$stripBidiMarks` now also strips U+200D (ZWJ) and U+FEFF (BOM) — both travel in alongside bidi control characters when text is copied from Word / Office.

### Fixed

- `NumberToWords::convert()` no longer silently truncates floats with magnitude above `PHP_INT_MAX`. The float path now throws `AbzarFormatException` with `ErrorCode::NUMBER_TO_WORDS_OUT_OF_RANGE`. The prior raw `\OverflowException` thrown from the scale-exhaustion branch is now the same `AbzarFormatException`, honoring the `catch (AbzarException $e)` contract.
- `NumberToWords::convert()` now throws `AbzarFormatException` with `ErrorCode::NUMBER_TO_WORDS_PRECISION_LOSS` when a float carries more than `PHP_FLOAT_DIG` significant digits. IEEE-754 has already rounded at that point — we'd otherwise return a plausibly-wrong word.

### Docs

- Version pin in `README.md` stability section updated to `^0.4@beta`, matching the install instructions and API-stability page.
- README feature matrix and examples updated for `PlateNumber`, `PersianCollator`, `HalfSpaceFixer`, and the new `fake` / `extractAll` helpers.

### Error-code additions

`ErrorCode::NATIONAL_ID_LIKELY_TRUNCATED`, `CARD_NUMBER_UNKNOWN_BIN`, `PHONE_NUMBER_UNKNOWN_OPERATOR`, `PLATE_NUMBER_EMPTY`, `PLATE_NUMBER_INVALID_FORMAT`, `PLATE_NUMBER_UNKNOWN_LETTER`, `PLATE_NUMBER_UNKNOWN_CITY_CODE`, `NUMBER_TO_WORDS_PRECISION_LOSS`, `ENV_MISSING_EXT_INTL`.

### Changed (breaking — 0.x)

- **API shape refactor.** All seven validators (`NationalId`, `CardNumber`, `Iban`, `LegalId`, `PhoneNumber`, `PostalCode`, `BillId`) now expose `::from($input): static` and `::tryFrom($input): ?static` value-object constructors in addition to the existing `::validate(): ValidationResult`. Instances are `Stringable` + `JsonSerializable` with typed accessors (e.g. `$ni->city()`, `$card->bin()`, `$phone->isMobile()`).
- **`ValidationResult` factories renamed** to eliminate the old positional-details-vs-warnings ambiguity:
  - `ValidationResult::success($details, $warnings)` → `ValidationResult::valid($detail?)` + `ValidationResult::validWithWarnings($warnings, $detail?)`.
  - `ValidationResult::failure($errors, …)` → `ValidationResult::invalid($errors, $warnings?)`.
- **Typed detail DTOs** replace `array<string, mixed>`. `ValidationResult::details(): array` is gone; `ValidationResult::detail(): ?JsonSerializable` returns a per-validator `readonly` DTO under `Eram\Abzar\Validation\Details\` (`NationalIdDetails`, `CardNumberDetails`, `IbanDetails`, `PhoneNumberDetails`, `BillIdDetails`, `PostalCodeDetails`). `ValidationResult::bank() / operator() / province()` helpers were removed — access them via the value object (e.g. `$card->bankEnum()`) or DTO property.
- **Single exception hierarchy.** Every library-raised exception now extends the new abstract `Eram\Abzar\AbzarException`, which carries an `errorCode(): ErrorCode`. Formatters throw `AbzarFormatException` (previously plain `\InvalidArgumentException`); VO constructors throw `AbzarValidationException` (new; also exposes the originating `ValidationResult`). `catch (AbzarException $e)` now covers every failure uniformly.
- **Result-vs-throw policy documented.** `docs/en/api-stability.md` gained a "Result-vs-throw policy" section. Short version: validators return a result, formatters fail fast.

### Added

- `Eram\Abzar\Validation\BillType` — backed enum replacing the `BillId::TYPES` string map. Cases: `WATER`, `ELECTRIC`, `GAS`, `PHONE`, `MOBILE`, `TAX`, `SERVICES`, `PASSPORT`, `OTHER`. `BillIdDetails::$type` now holds this enum directly; `BillId::type()` returns `BillType`.
- `Eram\Abzar\Validation\PhoneNumberType` — backed enum (`MOBILE` / `LANDLINE`) replacing raw strings on `PhoneNumberDetails::$type` / `PhoneNumber::type()`.
- `PhoneNumberDetails::mobile()` / `::landline()` named constructors — the direct constructor is private; the two factories make mobile/landline variants structurally unambiguous.
- Canonical input now carried on each detail DTO (`NationalIdDetails::$value`, `CardNumberDetails::$value`, `IbanDetails::$value`). Value objects read through to the DTO rather than storing a redundant second copy.

### Removed

- `ValidationResult::success()`, `ValidationResult::failure()`, and `ValidationResult::details()` — superseded by the named factories and typed DTO accessor above.
- `ValidationResult::bank()`, `::operator()`, `::province()` shortcut accessors — fetch from the value object or detail DTO instead.
- Private `NationalId::canonicalize()` and `Iban::canonicalize()` — the canonical string now lives on the detail DTO, so no second normalization pass is needed on the `::from()` happy path.

## [0.3.1-beta] — 2026-04-16

### Fixed

- `NumberToWords::convert()` now preserves leading zeros in the fractional part. `3.05` renders as `سه ممیز صفر پنج` (previously collapsed to `سه ممیز پنج`). Output is a **behavioural break** relative to 0.3.0-beta — acceptable under the documented `0.x` stability policy.
- `WordsToNumber::parse()` accepts leading `صفر` tokens after `ممیز` and counts them as zero-padding, so round-tripping `3.05` through `NumberToWords` → `WordsToNumber` now yields `3.05` exactly. Previously these inputs returned `null`.
- `ErrorCode::PHONE_NUMBER_INVALID_FORMAT` message no longer claims mobile-only. `PhoneNumber::validate()` has accepted landlines since 0.3.0-beta; the message is now `شماره تلفن باید یک شماره موبایل یا تلفن ثابت ایرانی معتبر باشد`. Error code value (`PHONE_NUMBER.INVALID_FORMAT`) is unchanged.

### Added

- `PersianNumerals::SCALES` extended with `کوینتیلیون` (10¹⁸), covering the full `int` range up to `PHP_INT_MAX`. `WordsToNumber` lookup updated in lockstep.
- `NumberToWords::convert()` now throws `OverflowException` if a group lands past the largest known scale, instead of silently truncating.

### Docs

- Installation and API-stability pages now recommend pinning `^0.3@beta`.
- `docs/en/related.md` no longer claims persian-tools parity tests are planned (they shipped in 0.3.0-beta) and no longer hedges the structured-error-codes row.

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
- `Eram\Abzar\Format\Currency` + `CurrencyUnit` — Toman / Rial formatter and converter.
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
