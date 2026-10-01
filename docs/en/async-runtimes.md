# Async Runtimes (Octane / RoadRunner / Swoole / ReactPHP)

Abzar is safe to use inside long-running PHP workers. This page documents the specific guarantees and the internal state you should be aware of.

## Summary

- **No request-scoped state.** Abzar never stores request inputs, user IDs, or session data across calls.
- **Static caches are pure.** Every process-wide cache is filled from source code alone and never from caller input, so nothing can leak between requests:
  - `DataSources` — the bundled lookup tables (`src/Data/*.php`), loaded once per table.
  - `PersianLookup::fromPersian()` — the per-enum Persian-name index for `Bank` / `Operator` / `Province`.
  - Default-option `CharNormalizer` instances held by `Slug`, `Province` and `PlateNumber`.
  - `WordsToNumber`'s word table and `KeyboardFixer`'s reverse layout map.
- **No global configuration.** There is no `setLocale()`, `setConfig()`, or similar mutation point. Every function takes its input explicitly.
- **Thread-safety** (Swoole coroutines, parallel worker threads) follows PHP's general model: each worker owns its classes and statics. Abzar does not mutate those statics after construction, so concurrent reads are safe.

## What this means in practice

### Laravel Octane

No special setup is required. You can use abzar freely inside controllers, form requests, jobs, and listeners. No entries are needed in `config/octane.php` for `listeners`, `warm`, `flush`, or `reset`.

### RoadRunner

Same — no warmup or reset hooks. Abzar is stateless from worker startup to shutdown.

### Swoole

Abzar functions are safe to call inside coroutines. Because there's no mutable state, there is no need to wrap calls in channels or mutexes.

### ReactPHP

Abzar is CPU-bound and synchronous. Calls return immediately; there is no I/O, so no promise integration is required.

## When to worry

You should revisit this page if:

- You subclass `CharNormalizer` and introduce mutable instance state.
- You keep a `CharNormalizer` instance alive across requests yourself. (It is a value object — instantiating a new one per call is cheap and recommended.)
- A future release of abzar introduces configurable global lookups (pluggable bank tables, etc.) — at that point the pattern will be documented here.
