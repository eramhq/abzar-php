---
title: "آشنایی با ابزار"
description: "شروع کار با ابزار و راهنمای امکانات فارسی و ایرانی در PHP."
---

# آشنایی با ابزار

ابزار برای اعتبارسنجی داده‌های ایرانی، کار با متن و ارقام فارسی، نمایش عدد و محاسبه مبلغ در PHP ساخته شده است. به فریم‌ورک خاصی وابسته نیست. PHP 8.1 به بالا و `mbstring` لازم است؛ `intl` فقط برای مرتب‌سازی فارسی و NFC استفاده می‌شود. جزئیات در [راهنمای نصب](installation.md) آمده است.

## شروع سریع

بعد از نصب، این کد را در `example.php` کنار `vendor/` ذخیره کنید و با `php example.php` اجرا کنید:

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

پنج ریال باقی‌مانده در نمایش تومان حفظ شده است. `Amount::inToman()` فقط بخش صحیح تومان را برمی‌گرداند. اگر شماره نامعتبر باشد، `PhoneNumber::from()` خطا می‌اندازد؛ برای فرم‌ها از `validate()` استفاده کنید.

## از کجا شروع کنم؟

- [اعتبارسنجی](validation.md): نتیجه، جزئیات و هشدارهای lookup.
- [متن فارسی](persian-text.md)، [ارقام](digits.md) و [اصلاح کیبورد](keyboard-fixer.md).
- [نمایش عدد و زمان](formatting.md)، [حروف به عدد](words-to-number.md) و [تومان و ریال](currency.md).
- [مدیریت خطا](error-handling.md) و [جدول کدهای خطا](error-codes.md).
- [اتصال به فریم‌ورک](framework-integration.md) و [workerهای طولانی‌مدت](async-runtimes.md).
- [سازگاری API](api-stability.md)، [تفاوت با persian-tools](persian-tools-parity.md) و [پروژه‌های مرتبط](related.md).

## امکانات

| بخش | API | کاربرد |
|---|---|---|
| اعتبارسنجی | `NationalId`, `LegalId` | کد ملی و شناسه ملی اشخاص حقوقی |
| اعتبارسنجی | `CardNumber`, `Iban` | شماره کارت و شبا، همراه با lookup بانک |
| اعتبارسنجی | `PhoneNumber`, `PostalCode` | تلفن همراه و ثابت، کد پستی |
| اعتبارسنجی | `BillId`, `PlateNumber` | شناسه قبض و پرداخت، پلاک خودرو |
| نتیجه | `ValidationResult`, `ErrorCode`, `ValidationDetail` | نتیجه مشترک، کد خطا و DTO جزئیات |
| enum | `Bank`, `Operator`, `Province`, `PlateType` | مقادیر مشخص برای بانک، اپراتور، استان و نوع پلاک |
| نمایش | `NumberFormatter`, `NumberToWords`, `WordsToNumber` | جداکننده هزارگان، عدد به حروف و برعکس |
| نمایش | `OrdinalNumber`, `TimeAgo` | عدد ترتیبی و زمان نسبی |
| پول | `Amount`, `Currency`, `Unit` | محاسبه مبلغ و نمایش تومان و ریال |
| متن | `CharNormalizer`, `HalfSpaceFixer`, `Slug` | یکسان‌سازی حروف، نیم‌فاصله و اسلاگ |
| متن | `Script`, `KeyboardFixer`, `PersianCollator` | بررسی کاراکترها، اصلاح کیبورد و مرتب‌سازی |
| ارقام | `DigitConverter` | تبدیل ارقام فارسی، عربی و انگلیسی |

## محدودیت‌ها

هیچ استعلامی از ثبت احوال، بانک یا اپراتور انجام نمی‌شود. معتبر بودن ساختار یا checksum، هویت و مالکیت را ثابت نمی‌کند. جدول‌ها snapshot هستند و ممکن است اطلاعات جدید را نداشته باشند. نرمال‌سازی متن موتور جستجو نیست. ابزار تقویم شمسی یا اتصال آماده به فریم‌ورک ندارد و هنوز به نسخه `1.0` نرسیده است.

در نمونه‌های کوتاه فرض شده Composer autoloader را بارگذاری کرده‌اید. کامنت کنار یک expression مقدار برگشتی را نشان می‌دهد؛ خود expression چیزی چاپ نمی‌کند. نمونه‌های کامل با بلوک `text` خروجی چاپ‌شده را نشان می‌دهند. کدهای فریم‌ورک در برنامه مربوط اجرا می‌شوند.
