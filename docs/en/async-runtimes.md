# Async Runtimes (Octane / RoadRunner / Swoole / ReactPHP)

Abzar is safe to use inside long-running PHP workers. This page documents the specific guarantees and the internal state you should be aware of.

## Summary

- **No request-scoped state.** Abzar never stores request inputs, user IDs, or session data across calls.
- **Static caches are pure.** The only process-wide cache is `Slug::defaultNormalizer()`, which holds a stateless `CharNormalizer` instance constructed with default options. It depends on nothing but source code and cannot leak between requests.
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
