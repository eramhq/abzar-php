# Contributing to Abzar

Abzar is a small, tightly scoped library of Persian-language utilities. Bug reports, new validators, additional locale / operator / bank data, and doc fixes are welcome.

## Before you start

- **File an issue first** for anything bigger than a typo or a one-line bug fix. Abzar deliberately stays narrow (no WordPress coupling, no framework integrations, no heavyweight dependencies) — a quick sanity check saves you from writing code that won't land.
- **Keep attribution intact.** The bank / BIN / city tables are derived from [persian-tools](https://github.com/persian-tools/persian-tools). When updating them, preserve the source-version comment in the relevant class docblock.

## Running the test suite

```bash
composer install
composer test              # phpunit
composer phpstan           # phpstan level 8
```

All PRs must be clean against the bundled PHPStan configuration and pass on the PHP 8.1–8.5 matrix in CI.

## Code style

- PHP 8.1+
- PSR-4 autoloading under `Eram\Abzar\…`
- Value types (`ValidationResult`) are `final` with `readonly` properties.
- Prefer explicit exceptions over `null` returns when the caller must know about the failure — ship a safe variant (`normalize`, `tryFoo`) alongside.
- Public error messages are Persian and treated as data, not i18n strings; don't wrap them in translation calls.

## Adding a new validator

1. Add the class under `src/Validation/<Name>.php`.
2. Return `ValidationResult::valid($detail)`, `ValidationResult::validWithWarnings(ErrorCode::…, $detail)` or `ValidationResult::invalid(ErrorCode::…)`, with a readonly DTO under `src/Validation/Details/` implementing `ValidationDetail` — do not invent a different result shape. Every new failure or warning gets its own `ErrorCode` case (plus a snapshot in `ErrorCodeMessageSnapshotTest`).
3. Always accept Persian and Arabic digits and pasted noise (NBSP, bidi marks, Unicode dashes): run the input through `Eram\Abzar\Internal\ErrorInput::digits()` before regex / arithmetic.
4. Add unit tests under `tests/Unit/Validation/<Name>Test.php` mirroring the conventions in the existing tests (Persian-digit case, Arabic-digit case, empty case, edge cases around the checksum).
5. Document the new class in both locale trees under `docs/`, update the overview and `docs/navigation.json`, and run `composer docs:check` and `composer docs:examples`.

## Adding to a data table

The BIN / city / operator / plate tables in `src/Data/` (loaded through `Eram\Abzar\Data\DataSources`) are snapshots of a specific upstream release. When adding new entries:

- Include the issuer name in Persian exactly as upstream (banks change brand names; use the name from the cited persian-tools version).
- Add a test case that asserts the new entry resolves to the expected bank / city / operator. Persian names must resolve through the matching enum's `fromPersian()` — `DataSourcesTest::test_every_bundled_name_resolves_to_an_enum` fails otherwise; add a `persianAliases()` entry when a table must keep a variant spelling.
- Update the docblock that cites the source version if you're moving to a newer upstream snapshot.

## Commit style

- Imperative subject, ≤70 chars.
- One logical change per commit.
- Don't commit `.phpunit.cache/` or editor droppings.

## PR checklist

- [ ] `composer test` passes on PHP 8.1+
- [ ] `composer phpstan` passes at level 8
- [ ] New public API has tests
- [ ] Both locale guides and navigation updated; `composer docs:check` and `composer docs:examples` pass
- [ ] Attribution headers on data tables are intact

## Questions

Open an issue at <https://github.com/eramhq/abzar-php/issues>.

## Documentation

See [documentation maintenance](docs/README.md) for translated paths, metadata, examples, checks and the release-only website importer contract. Public guides are sourced from the exact commit of a published release tag.
