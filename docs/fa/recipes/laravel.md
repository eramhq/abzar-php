---
title: "استفاده در Laravel"
description: "ساخت rule و استفاده از نتیجه ابزار در FormRequest لاراول."
---

# استفاده در Laravel

ابزار bridge آماده برای Laravel ندارد. این adapterها را در برنامه خودتان قرار دهید. نمونه از قرارداد `ValidationRule` در Laravel 10 به بالا استفاده می‌کند و به برنامه راه‌اندازی‌شده Laravel نیاز دارد.

## کلاس rule

کلاس را در `app/Rules/IranianNationalId.php` ذخیره کنید:

```php
<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Eram\Abzar\Validation\NationalId;
use Illuminate\Contracts\Validation\ValidationRule;

final class IranianNationalId implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('کد ملی باید رشته باشد.');
            return;
        }

        $result = NationalId::validate($value);
        if (!$result->isValid()) {
            foreach ($result->errors() as $error) {
                $fail($error);
            }
        }
    }
}
```

در FormRequest:

```php
public function rules(): array
{
    return [
        'national_id' => ['required', 'string', new \App\Rules\IranianNationalId()],
    ];
}
```

## closure برای یک فیلد

قاعده `bail` جلوی اجرای closure بعد از شکست بررسی نوع را می‌گیرد:

```php
public function rules(): array
{
    return [
        'iban' => ['bail', 'required', 'string', function (string $attr, mixed $value, \Closure $fail): void {
            $result = \Eram\Abzar\Validation\Iban::validate((string) $value);
            if (!$result->isValid()) {
                $fail(implode('؛ ', $result->errors()));
            }
        }],
    ];
}
```

## ثبت extension

در `AppServiceProvider::boot()` ثبت کنید:

```php
// In AppServiceProvider::boot()
\Illuminate\Support\Facades\Validator::extend('iranian_mobile', function ($attribute, $value, $parameters, $validator) {
    if (!is_string($value)) {
        return false;
    }
    $phone = \Eram\Abzar\Validation\PhoneNumber::tryFrom($value);
    return $phone !== null && $phone->isMobile();
}, 'شماره موبایل معتبر نیست.');
```

سپس از `'phone' => ['required', 'string', 'iranian_mobile']` استفاده کنید. برای پذیرش تلفن ثابت، شرط `isMobile()` را بردارید و نام rule و پیام را تغییر دهید.

## نگه‌داشتن value object

اگر بعدا به بانک یا BIN نیاز دارید، object را در hook فرم بسازید. این فراخوانی دوباره اعتبارسنجی می‌کند و نتیجه قبلی را مصرف نمی‌کند. نوع ورودی و خطاهای قبلی را بررسی کنید:

```php
public function after(): array
{
    return [function (\Illuminate\Validation\Validator $validator): void {
        if ($validator->errors()->has('card')) {
            return;
        }
        $input = $this->input('card');
        $card = is_string($input) ? \Eram\Abzar\Validation\CardNumber::tryFrom($input) : null;
        if ($card !== null) {
            $this->merge(['_card' => $card]); // access via $card->bank(), $card->bin()
        }
    }];
}
```

## خروجی

با بارگذاری کلاس rule در برنامه Laravel:

```php
$validator = \Illuminate\Support\Facades\Validator::make(
    ['national_id' => '1234567890'],
    ['national_id' => ['required', 'string', new \App\Rules\IranianNationalId()]],
);
echo $validator->errors()->first('national_id');
```

```text
کد ملی نامعتبر است
```

ورودی `0013542419` از این rule عبور می‌کند. فیلد خالی را `required` خود Laravel بررسی می‌کند. مقدار ذخیره‌شده در request خودکار نرمال نمی‌شود؛ بعد از موفقیت، برای ذخیره از `NationalId::from($value)->value()` استفاده کنید. هشدار lookup پذیرفته می‌شود و این بررسی تایید هویت نیست.

قرارداد فریم‌ورک در [مستندات rule سفارشی Laravel](https://laravel.com/docs/11.x/validation#custom-validation-rules) آمده است.

مطالب مرتبط: [اتصال به فریم‌ورک](../framework-integration.md)، [اعتبارسنجی](../validation.md)، [خطاها](../error-handling.md).
