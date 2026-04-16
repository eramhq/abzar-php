# Changelog

All notable changes to this project are documented in this file. The format is loosely based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html) once it leaves `0.x`.

## [Unreleased]

### Fixed

- `NumberToWords::convert()` no longer silently truncates floats with magnitude above `PHP_INT_MAX`. The float path now throws `AbzarFormatException` with `ErrorCode::NUMBER_TO_WORDS_OUT_OF_RANGE`. The prior raw `\OverflowException` thrown from the scale-exhaustion branch is now the same `AbzarFormatException`, honoring the `catch (AbzarException $e)` contract.

### Docs

- Version pin in `README.md` stability section updated to `^0.4@beta`, matching the install instructions and API-stability page.

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
