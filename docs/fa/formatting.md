---
title: "نمایش عدد و زمان"
description: "جداکننده هزارگان، عدد به حروف، عدد ترتیبی و زمان نسبی."
---

# نمایش عدد و زمان

بعد از بررسی ورودی، از formatterها برای نمایش خواناتر مقدار در رابط کاربری استفاده کنید.

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Format\{NumberFormatter, NumberToWords, OrdinalNumber, TimeAgo};

echo NumberFormatter::withSeparators('۱۲۳۴٫۵۰'), "\n";
echo NumberToWords::convert(1234), "\n";
echo NumberToWords::convert(3.25), "\n";
echo OrdinalNumber::toWord(3), "\n";
echo OrdinalNumber::toShort(43), "\n";
echo TimeAgo::format(1_700_000_000, now: 1_700_000_300), "\n";
```

```text
1,234.50
یک هزار و دویست و سی و چهار
سه ممیز بیست و پنج
سوم
۴۳ام
۵ دقیقه پیش
```

## ورودی عددی

`NumberFormatter::withSeparators()` ورودی `int|float|string` می‌گیرد. ارقام فارسی و عربی، جداکننده هزارگان و `٫` را یکسان می‌کند و string با ارقام انگلیسی برمی‌گرداند. عدد اعشاری ساده پذیرفته می‌شود، اما نماد علمی یا پسوند واحد پول نه. جای جداکننده‌ها بررسی نمی‌شود و فقط حذف می‌شوند. decimal string صفرهای آخر را حفظ می‌کند؛ float ممکن است از قبل دقت خود را از دست داده باشد یا به شکل علمی تبدیل به string شود.

`NumberToWords::convert()` ورودی `int|float` می‌گیرد، نه string با ارقام فارسی. float حداکثر با ده رقم اعشار نمایش داده می‌شود؛ بررسی دقت و محدوده ممکن است `FormatException` ایجاد کند. این متد ابزار محاسبات اعشاری با دقت نامحدود یا روش ذخیره بدون افت دقت نیست. برای جهت برعکس [حروف به عدد](words-to-number.md) را ببینید.

## عدد ترتیبی و زمان

متدهای تبدیل عدد ترتیبی، integer مثبت لازم دارند؛ صفر و عدد منفی `FormatException` ایجاد می‌کنند. `toShort()` به طور پیش‌فرض از ارقام فارسی و `ام` استفاده می‌کند. اگر پسوند انگلیسی بدهید، انتخاب درست آن با خود شماست.

`TimeAgo::format()` یک Unix timestamp، شیء `DateTimeInterface` یا string قابل پردازش با `strtotime()` می‌گیرد. برای تست ثابت، `now` را مشخص کنید. پردازش string به timezone تنظیم‌شده در PHP وابسته است و تاریخ شمسی را نمی‌خواند. بازه ماه و سال با مدت ثابت و تقریبی محاسبه می‌شود. callback اختیاری `jalaliMonthResolver` برای فاصله یک سال یا بیشتر، متن تقویم را از برنامه شما می‌گیرد.

مطالب مرتبط: [پول](currency.md)، [ارقام](digits.md)، [مدیریت خطا](error-handling.md).
