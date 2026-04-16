# Installation

## Requirements

- PHP 8.1 or later (8.1, 8.2, 8.3, 8.4 are all tested).
- `ext-mbstring` (ships enabled in virtually every PHP distribution).
- No other runtime dependencies.

## Install via Composer

```bash
composer require eram/abzar:^0.3@beta
```

While Abzar is in `0.x`, pin the beta range to opt into stability updates without accidentally jumping a breaking minor.

## Optional companions

- [`eramhq/daynum`](https://github.com/eramhq/daynum) — jalali / shamsi calendar utilities. If you need Persian dates alongside abzar, install it separately:
  ```bash
  composer require eramhq/daynum
  ```
  Abzar references it through `composer suggest` but never bundles it.

- [`eramhq/persian-kit`](https://github.com/eramhq/persian-kit) — WordPress plugin that wires abzar into WP hooks (`the_content`, `sanitize_title`, `pre_get_posts`, etc.).

## Verifying the install

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\NationalId;

var_dump(NationalId::validate('0013542419')->isValid()); // bool(true)
```
