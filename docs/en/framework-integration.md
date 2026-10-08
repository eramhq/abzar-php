---
title: "Framework integration"
description: "Wrap Abzar in application-owned adapters for Laravel, Symfony and WordPress."
---

# Framework integration

Abzar uses Composer autoloading and does not install service providers, framework rules or plugins. Keep small adapters in your application so its validation policy stays explicit.

The library-only part of a phone form rule can run independently:

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\PhoneNumber;

function mobileError(mixed $value): ?string
{
    if (!is_string($value)) {
        return 'Phone must be a string';
    }
    $result = PhoneNumber::validate($value);
    if (!$result->isValid()) {
        return $result->errors()[0];
    }
    return PhoneNumber::from($value)->isMobile() ? null : 'Mobile number required';
}

var_dump(mobileError('09121234567'));
echo mobileError('02188887777'), "\n";
```

```text
NULL
Mobile number required
```

The adapter rejects non-string input, accepts lookup warnings, and requires a mobile rather than any valid phone. It does not send an OTP. Use `isStrictlyValid()` only if unresolved prefixes must be rejected by your application policy.

## Recipes

- [Laravel](recipes/laravel.md): ValidationRule, closures, provider extensions and FormRequest hooks.
- [Symfony Validator](recipes/symfony.md): a custom constraint and DTO.
- [Symfony Console](recipes/symfony-console.md): a line-by-line bulk checker.
- [WordPress](recipes/wordpress.md): slug/content hooks, REST updates and search query normalization.

Framework recipes require the corresponding framework, autoloading and application bootstrapping. They are not executable as standalone Abzar scripts. Required/nullable field handling, access control, persistence and UI messages remain application responsibilities. Validate types before calling string-only APIs; do not cast an array from a request to a string.

Related: [validation](validation.md), [errors](error-handling.md), [worker lifecycle](async-runtimes.md).
