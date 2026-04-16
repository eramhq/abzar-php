# Changelog

All notable changes to this project are documented in this file. The format is loosely based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html) once it leaves `0.x`.

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
- `Eram\Abzar\Format\OrdinalNumber` — Persian ordinals (`توWord`, `toShort`, `addSuffix`).
- `Eram\Abzar\Format\TimeAgo` — fuzzy relative time in Persian.
- `Eram\Abzar\Text\Script` — `isPersian` / `hasPersian` / `isArabic` / `hasArabic` detectors with basic / complex modes.
- `Eram\Abzar\Text\Slug` — Persian-aware slug generator.
- `Eram\Abzar\Text\CharNormalizer` — Arabic → Persian character and digit normalization; HTML-aware `normalizeContent()`; opt-in Teh-Marbuta → Heh conversion.
- `Eram\Abzar\Digits\DigitConverter` — Persian / English / Arabic digit conversion; HTML-aware `convertContent()`.

### Notes

- Requires PHP 8.1+. No runtime Composer or PHP-extension dependencies beyond `mbstring`.
- MIT licensed. Validation data tables originate from the MIT-licensed [persian-tools](https://github.com/persian-tools/persian-tools) project.
- Error messages are Persian. Language-neutral error codes are planned for a later release; `0.x` is a byte-identical extraction of the upstream Persian Kit implementation.
