# Abzar

[فارسی](README.fa.md)

Persian text, digits, number formatting, Iranian validators, and rial/toman money tools for PHP 8.1+. No third-party Composer packages are required at runtime. PHP's `mbstring` extension is required; `intl` is optional for Persian sorting and NFC normalization.

## Install

```bash
composer require 'eram/abzar:^0.8.1'
```

Abzar remains pre-1.0 (`0.x`); minor releases may break compatibility. Release `0.8.1` has no prerelease suffix. The constraint above stays below `0.9`. Review the [stability policy](docs/en/api-stability.md) before upgrading.

## Quick start

Save as `example.php` next to `vendor/` and run `php example.php`.

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\PhoneNumber;
use Eram\Abzar\Money\Amount;
use Eram\Abzar\Money\Currency;

$phone = PhoneNumber::from('+98 912 123 4567');
echo $phone->value(), "\n";
echo Currency::format(Amount::fromRials(12_345)), "\n";
```

```text
09121234567
۱،۲۳۴.۵ تومان
```

Validation checks structure and, where applicable, checksums. It does not prove ownership, identity, account existence, or phone reachability.

## Documentation

- [English documentation](docs/en/overview.md) · [مستندات فارسی](docs/fa/overview.md)
- [Installation](docs/en/installation.md) · [Validation](docs/en/validation.md) · [Errors and warnings](docs/en/error-handling.md)
- [Persian text](docs/en/persian-text.md) · [Digits](docs/en/digits.md) · [Formatting](docs/en/formatting.md) · [Money](docs/en/currency.md)
- [Framework integration](docs/en/framework-integration.md) · [Upgrade guide](UPGRADE.md) · [Changelog](CHANGELOG.md)

MIT; see [LICENSE](LICENSE). Some algorithms and lookup tables derive from the MIT-licensed [persian-tools](https://github.com/persian-tools/persian-tools) project. See [Contributing](CONTRIBUTING.md) to help.
