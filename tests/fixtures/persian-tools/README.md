# persian-tools JS fixtures

This directory holds a copy of the upstream [persian-tools](https://github.com/persian-tools/persian-tools) JS test fixtures, used by the abzar contract tests to assert parity on the Luhn / mod-97 / national-id checksums and operator lookups.

## Pulling fixtures

The fixtures are **not vendored** — the checkout is deliberate. Run:

```
composer fixtures:pull
```

…to sync `tests/fixtures/persian-tools/` from the pinned upstream SHA. The SHA lives in `tools/fixtures/pull.sh`. Bumping it is a deliberate PR.

Contract tests skip themselves when this directory is empty, so local development without the fixtures still runs a green `composer test`.

## License

The upstream project is MIT. When the fixtures are pulled in, the upstream `LICENSE` is copied alongside them at `tests/fixtures/persian-tools/LICENSE`.
