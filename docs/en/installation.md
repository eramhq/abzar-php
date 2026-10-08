---
title: "Installation"
description: "Install Abzar with Composer and check required PHP extensions."
---

# Installation

## Requirements

- PHP 8.1 or later. The repository CI matrix covers PHP 8.1–8.5.
- `ext-mbstring` is required by `composer.json`; do not assume your PHP distribution enables it.
- `ext-intl` is optional. It is required for `PersianCollator` and `new CharNormalizer(normalizeToNfc: true)`. Calling those without it throws `EnvironmentException` with `ENV.MISSING_EXT_INTL`.
- No third-party Composer runtime packages. Development tools have their own requirements.

## Install via Composer

```bash
composer require 'eram/abzar:^0.8@beta'
composer check-platform-reqs
php -m
```

`^0.8@beta` allows compatible `0.8` updates below `0.9` and opts into beta packages; it does not pin one exact release. Commit your application's `composer.lock` for reproducible installs. Never bypass platform checks to hide a missing extension. The web server and CLI can use different PHP configurations.

## Verify the install

Save `example.php` beside `vendor/`, then run `php example.php`:

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\NationalId;

var_dump(NationalId::validate('0013542419')->isValid());
```

```text
bool(true)
```

This checks the ID's structure and checksum, not a person's identity.

## Optional companions

`eram/daynum` is listed in Composer's suggestions for Jalali calendar utilities; it is not installed with Abzar. See [related projects](related.md) and the [WordPress recipe](recipes/wordpress.md) for adjacent work.

Next: [quick start](overview.md), [validation](validation.md), [API stability](api-stability.md).
