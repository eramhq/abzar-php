# Changelog

All notable changes to this project are documented in this file. The format is loosely based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html) once it leaves `0.x`.

## [Unreleased]

### Added

- `declare(strict_types=1)` on every source file — silent numeric coercion is now a type error.
- `ValidationResult` now implements `JsonSerializable` and `Stringable`; `jsonSerialize()` returns `{valid, errors, details}` and `__toString()` joins Persian error messages with `; `.
- `Slug::generate()` accepts an optional `CharNormalizer` argument, so callers can pass a custom-configured normalizer (e.g. `tehMarbuta: true`) without hitting a shared default-config cache.
- `Eram\Abzar\Text\HtmlSegmenter` — internal helper that splits HTML into tag vs. text segments, shared by `CharNormalizer::normalizeContent()` and `DigitConverter::convertContent()`. HTML comments are now uniformly preserved by both.
- `composer suggest` for [`eramhq/daynum`](https://github.com/eramhq/daynum) (jalali / shamsi calendar).
- `SECURITY.md`, GitHub issue / PR templates, and Dependabot configuration.
- `friendsofphp/php-cs-fixer` dev dependency, `.php-cs-fixer.dist.php` config, `composer cs-check` / `composer cs-fix` scripts, and a CI style-check job.
- `docs/en/`: installation, API stability policy, async-runtime safety note, and integration recipes for Laravel FormRequest, Symfony Validator, Symfony Console, and WordPress.
- README: "Related packages", "Versus other Persian PHP libraries" comparison table, and "Framework bridges" section pointing to the recipes.
- Algorithm citation on `LegalId::COEFFICIENTS` referencing the Iranian legal-entity ID specification.

### Changed

- All static-only classes (`Slug`, `Script`, `DigitConverter`, `NumberFormatter`, `NumberToWords`, `OrdinalNumber`, `TimeAgo`, and the validators) now have a `private __construct()` and are declared `final`; they can no longer be instantiated or subclassed.
- Exception messages in `NumberFormatter::withSeparators()` and `TimeAgo::format()` now strip control characters and truncate user input before interpolation, to avoid leaking or log-injecting raw inputs.
- `composer.json` now explicitly requires `ext-mbstring`.

## [0.1.0-beta] — 2026-04-16

Initial release. Fourteen utility classes extracted verbatim from [`eramhq/persian-kit`](https://github.com/eramhq/persian-kit) into a standalone, framework-agnostic PHP 8.1+ package.

### Added

- `Eram\Abzar\Validation\ValidationResult` — shared `{isValid, errors, details}` return type for validators.
- `Eram\Abzar\Validation\NationalId` — 10-digit Iranian national-ID checksum + city / province lookup (~640 code entries, persian-tools v5.0.0-beta.0 snapshot).
- `Eram\Abzar\Validation\LegalId` — 11-digit legal-entity ID checksum.
- `Eram\Abzar\Validation\PhoneNumber` — Iranian mobile number validation (`09xx`, `+98`, `0098`, `98` prefixes) with MCI / Irancell / RighTel / Taliya / Shatel / Aptel operator detection.
- `Eram\Abzar\Validation\CardNumber` — Luhn check for 16-digit bank cards + bank name from 6-digit BIN (49-bank table).
- `Eram\Abzar\Validation\Iban` — `IR`-prefixed IBAN mod-97 with bank lookup.
- `Eram\Abzar\Format\NumberFormatter` — thousands-separator formatter, handles Persian / Arabic digit input.
- `Eram\Abzar\Format\NumberToWords` — integer and decimal to Persian words; supports up to quadrillions.
- `Eram\Abzar\Format\OrdinalNumber` — Persian ordinals (`toWord`, `toShort`, `addSuffix`).
- `Eram\Abzar\Format\TimeAgo` — fuzzy relative time in Persian.
- `Eram\Abzar\Text\Script` — `isPersian` / `hasPersian` / `isArabic` / `hasArabic` detectors with basic / complex modes.
- `Eram\Abzar\Text\Slug` — Persian-aware slug generator.
- `Eram\Abzar\Text\CharNormalizer` — Arabic → Persian character and digit normalization; HTML-aware `normalizeContent()`; opt-in Teh-Marbuta → Heh conversion.
- `Eram\Abzar\Digits\DigitConverter` — Persian / English / Arabic digit conversion; HTML-aware `convertContent()`.

### Notes

- Requires PHP 8.1+. No runtime Composer or PHP-extension dependencies beyond `mbstring`.
- MIT licensed. Validation data tables originate from the MIT-licensed [persian-tools](https://github.com/persian-tools/persian-tools) project.
- Error messages are Persian. Language-neutral error codes are planned for a later release; `0.x` is a byte-identical extraction of the upstream Persian Kit implementation.
