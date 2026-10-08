---
title: "اتصال به فریم‌ورک"
description: "استفاده از ابزار در Laravel، Symfony و WordPress با adapter داخل برنامه."
---

# اتصال به فریم‌ورک

ابزار با Composer autoload می‌شود و service provider، rule یا پلاگین آماده نصب نمی‌کند. adapter کوچک را داخل برنامه نگه دارید تا قانون اعتبارسنجی روشن باشد.

بخش مستقل از فریم‌ورک یک rule موبایل می‌تواند این‌طور باشد:

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

این کد نوع غیر string را رد می‌کند، هشدار lookup را می‌پذیرد و فقط موبایل قبول می‌کند، نه هر شماره معتبر. OTP ارسال نمی‌کند. اگر قانون برنامه وجود پیش‌شماره در جدول را لازم می‌داند، از `isStrictlyValid()` استفاده کنید.

## راهنماها

- [Laravel](recipes/laravel.md): کلاس ValidationRule، closure، extension و FormRequest.
- [Symfony Validator](recipes/symfony.md): constraint سفارشی و DTO.
- [Symfony Console](recipes/symfony-console.md): بررسی فایل خط به خط.
- [WordPress](recipes/wordpress.md): hook اسلاگ و محتوا، REST و query جستجو.

این نمونه‌ها به فریم‌ورک مربوط، autoload و راه‌اندازی برنامه نیاز دارند و با نصب تنها ابزار اجرا نمی‌شوند. required و nullable، سطح دسترسی، ذخیره داده و پیام رابط کاربری با برنامه است. قبل از APIهای string نوع را بررسی کنید؛ آرایه ورودی request را به string تبدیل نکنید.

مطالب مرتبط: [اعتبارسنجی](validation.md)، [خطاها](error-handling.md)، [workerها](async-runtimes.md).
