# Abzar — English Documentation

Abzar is a zero-runtime-dependency Persian (Farsi) utility toolkit for PHP 8.1+. This directory holds longer-form English documentation. For a quick tour, see the project [README](../../README.md).

## Contents

- [Installation](installation.md)
- [API stability & backwards-compatibility policy](api-stability.md)
- [Async runtime safety (Octane / RoadRunner / Swoole)](async-runtimes.md)
- Reference
  - [Postal Code](postal-code.md)
  - [Bill ID](bill-id.md)
  - [Keyboard Fixer](keyboard-fixer.md)
  - [Words to Number](words-to-number.md)
  - [Currency](currency.md)
- [Integration recipes](recipes/)
  - [Laravel FormRequest](recipes/laravel.md)
  - [Symfony Validator](recipes/symfony.md)
  - [Symfony Console](recipes/symfony-console.md)
  - [WordPress](recipes/wordpress.md)
- [Related packages](related.md)

## Charter

Abzar ships the small set of Persian utilities every Iranian-facing PHP app reimplements: national-ID / IBAN / bank-card / phone validation, number-to-words and time-ago formatting, slug generation, script detection, and digit conversion.

What it is **not**:

- A jalali / shamsi calendar library — see [`eramhq/daynum`](https://github.com/eramhq/daynum).
- A WordPress plugin — see [`eramhq/persian-kit`](https://github.com/eramhq/persian-kit).
- A framework bridge (Laravel rule objects, Symfony validators) — write the thin adapter in your own application code; recipes below show how.
