---
title: "پلاک خودرو"
description: "خواندن ساختار پلاک خودرو و lookup نوع و استان."
---

# پلاک خودرو

`PlateNumber` الگوی `NN[letter]NNN-NN` را می‌خواند: دو رقم، بخش حرف، سه رقم و کد دو‌رقمی شهر.

## نمونه قابل اجرا

کد را کنار `vendor/` ذخیره و اجرا کنید:

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\PlateNumber;

$plate = PlateNumber::from('۱۲ ب ۳۴۵ - ۱۱');
echo $plate->value(), "\n";
echo $plate->province(), "\n";
```

```text
12ب345-11
تهران
```

## قواعد و نکته‌ها

فاصله، خط تیره و علامت‌های نامرئی حذف و ارقام یکسان‌سازی می‌شوند. بخش حرف در پیاده‌سازی یک تا سه حرف Unicode می‌پذیرد؛ `الف` هم جا می‌گیرد. `ي` و `ك` عربی به شکل فارسی تبدیل می‌شوند.

`12غ345-11` معتبر است، ولی هشدار `PLATE_NUMBER.UNKNOWN_LETTER` و نوع `PlateType::OTHER` دارد. `12ب345-00` هشدار `PLATE_NUMBER.UNKNOWN_CITY_CODE` و استان `null` دارد. هر دو هشدار می‌توانند هم‌زمان رخ دهند. `isStrictlyValid()` هر دو lookup را لازم می‌داند.

متدهای `twoDigit()`، `letter()`، `threeDigit()`، `cityCode()`، `type()`، `province()` و `provinceEnum()` جزئیات را می‌دهند. برای نمونه بالا نوع `PlateType::PRIVATE` و استان `Province::TEHRAN` است.

نوع‌ها شامل `PRIVATE`، `TAXI` (`ت`)، `PUBLIC` (`ع`)، `POLICE` (`پ`)، `GOVERNMENT` (`الف`)، `AGRICULTURAL` (`ک`)، `DISABLED` (`ژ`)، `MILITARY` (`ش`، `ث`، `ز`، `ف`)، `DIPLOMATIC`، `TEMPORARY` (`گ`) و `OTHER` هستند.

کدهای قبل از تقسیم استان ممکن است چند استان داشته باشند. برای `12ب345-21`، `province()` مقدار `تهران - البرز` و `provinces()` آرایه `['تهران', 'البرز']` را می‌دهد. `provinceEnum()` در این حالت `null` است؛ از `provinceEnums()` استفاده کنید. منبع جدول در [PlateCodes.php](../../src/Data/PlateCodes.php) ثبت شده است.

`extractAll()` پلاک‌های معتبر را از متن می‌گیرد. `fake()` نوع و کد شناخته‌شده انتخاب می‌کند و `fake(PlateType::TAXI)` نوع را مشخص می‌کند. `fake(PlateType::OTHER)` با `FAKE.INVALID_ARGUMENT` خطا می‌دهد.

این parser همه شکل‌های خاص پلاک را پوشش نمی‌دهد و استعلام خودرو یا مالکیت انجام نمی‌دهد. نمونه تصادفی ممکن است پلاک واقعی باشد.

## کدهای خطا

کدهای همین بخش و پیام دقیق کتابخانه در [جدول کدهای خطا](error-codes.md) آمده است. برای شرط‌های برنامه از کد استفاده کنید، نه متن پیام.

مطالب مرتبط: [اعتبارسنجی](validation.md)، [مدیریت خطا](error-handling.md)، [اتصال به فریم‌ورک](framework-integration.md).
