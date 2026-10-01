# persian-tools JS fixtures

This directory holds a vendored copy of the upstream [persian-tools](https://github.com/persian-tools/persian-tools) JS test suites (`test/*.spec.ts`), the source of the test vectors hand-lifted into `tests/Unit/Fixtures/PersianToolsContractTest.php` (each data provider cites its spec file and line) to assert parity on the Luhn / mod-97 / national-ID checksums, bill IDs, operator and bank lookups.

The files are committed, so `composer test` runs the contract tests without network access. Only the specs are vendored — the upstream `src/` tables (e.g. the numberplate dataset behind `src/Data/PlateCodes.php`) are not; their provenance is recorded in each data file's header.

## Refreshing

```
composer fixtures:pull
```

…re-syncs this directory from the upstream SHA pinned in `tools/fixtures/SHA` (the copy here, `SHA`, records what is currently vendored). Bumping the pin is a deliberate PR: re-run the suite and record any intentional divergence in the contract test.

The contract tests skip themselves when `SHA` is missing, i.e. when the directory has been emptied.

## License

The upstream project is MIT. Its `LICENSE` is copied alongside the specs at `tests/fixtures/persian-tools/LICENSE`.
