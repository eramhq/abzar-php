---
title: "نصب"
description: "نصب ابزار با Composer و بررسی نسخه PHP و افزونه‌های لازم."
---

# نصب

## پیش‌نیازها

- PHP 8.1 یا بالاتر؛ CI مخزن نسخه‌های 8.1 تا 8.5 را پوشش می‌دهد.
- افزونه `mbstring` طبق `composer.json` لازم است. فعال بودن آن را در تنظیمات PHP بررسی کنید.
- افزونه `intl` اختیاری است و برای `PersianCollator` و `new CharNormalizer(normalizeToNfc: true)` لازم می‌شود. استفاده از این قابلیت‌ها بدون آن، `EnvironmentException` با کد `ENV.MISSING_EXT_INTL` ایجاد می‌کند.
- هیچ پکیج دیگری در زمان اجرا لازم نیست. ابزارهای توسعه پیش‌نیازهای جداگانه دارند.

## نصب با Composer

```bash
composer require 'eram/abzar:^0.8@beta'
composer check-platform-reqs
php -m
```

محدوده `^0.8@beta` نسخه‌های سری `0.8` تا قبل از `0.9` را می‌پذیرد و اجازه نصب beta می‌دهد؛ یک نسخه دقیق را ثابت نمی‌کند. فایل `composer.lock` برنامه را نگه دارید تا نصب تکرارپذیر باشد. برای پنهان کردن کمبود افزونه از دور زدن platform check استفاده نکنید. تنظیمات PHP وب‌سرور و CLI ممکن است متفاوت باشند.

## بررسی نصب

کد زیر را در `example.php` کنار `vendor/` قرار دهید و با `php example.php` اجرا کنید:

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\NationalId;

var_dump(NationalId::validate('0013542419')->isValid());
```

```text
bool(true)
```

این نتیجه ساختار و checksum کد ملی را تایید می‌کند، نه هویت شخص را.

## پکیج‌های جداگانه

`eram/daynum` در پیشنهادهای Composer برای تقویم شمسی آمده است، اما همراه ابزار نصب نمی‌شود. [پروژه‌های مرتبط](related.md) و [راهنمای WordPress](recipes/wordpress.md) را هم ببینید.

مطالب مرتبط: [شروع سریع](overview.md)، [اعتبارسنجی](validation.md)، [سازگاری API](api-stability.md).
