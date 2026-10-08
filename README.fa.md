# ابزار (Abzar)

[English](README.md)

ابزار یک کتابخانه برای متن و ارقام فارسی، اعتبارسنجی داده‌های ایرانی، نمایش عدد و کار با تومان و ریال در PHP 8.1 به بالا است. به پکیج دیگری در زمان اجرا وابسته نیست. افزونه `mbstring` لازم است و `intl` فقط برای مرتب‌سازی فارسی و نرمال‌سازی NFC استفاده می‌شود.

## نصب

```bash
composer require 'eram/abzar:^0.8@beta'
```

ابزار هنوز beta و در سری `0.x` است. نسخه‌های minor ممکن است تغییر ناسازگار داشته باشند. دستور بالا نسخه‌ای پایین‌تر از `0.9` نصب می‌کند. قبل از ارتقا، [سیاست سازگاری API](docs/fa/api-stability.md) را بخوانید.

## شروع سریع

کد را در فایل `example.php` کنار پوشه `vendor/` ذخیره کنید و با `php example.php` اجرا کنید.

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

اعتبارسنجی فقط ساختار و در موارد لازم checksum را بررسی می‌کند. معتبر بودن ورودی به معنی تایید هویت، مالکیت، وجود حساب یا در دسترس بودن شماره تلفن نیست.

## مستندات

- [مستندات فارسی](docs/fa/overview.md) · [English documentation](docs/en/overview.md)
- [نصب](docs/fa/installation.md) · [اعتبارسنجی](docs/fa/validation.md) · [خطاها و هشدارها](docs/fa/error-handling.md)
- [متن فارسی](docs/fa/persian-text.md) · [ارقام](docs/fa/digits.md) · [نمایش عدد](docs/fa/formatting.md) · [پول](docs/fa/currency.md)
- [اتصال به فریم‌ورک](docs/fa/framework-integration.md) · [راهنمای ارتقا به انگلیسی](UPGRADE.md) · [تغییرات نسخه‌ها به انگلیسی](CHANGELOG.md)

مجوز پروژه MIT است؛ [LICENSE](LICENSE) را ببینید. بخشی از الگوریتم‌ها و جدول‌ها از پروژه [persian-tools](https://github.com/persian-tools/persian-tools) با مجوز MIT گرفته شده است.
