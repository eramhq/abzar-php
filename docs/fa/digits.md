---
title: "ارقام فارسی، عربی و انگلیسی"
description: "تبدیل شکل ارقام در string و HTML بدون تبدیل مقدار به عدد."
---

# ارقام فارسی، عربی و انگلیسی

`DigitConverter` برای نمایش ارقام یا آماده کردن string برای یک API عددی مناسب است. صفرهای اول و متن غیرعددی را حفظ می‌کند.

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Digits\DigitConverter;

echo DigitConverter::toEnglish('۰۰۱٢'), "\n";
echo DigitConverter::toPersian('Version 1.2'), "\n";
echo DigitConverter::toArabic('۱۲3'), "\n";
echo DigitConverter::convertContent('<a href="page-5">Item 5</a><code>123</code>'), "\n";
```

```text
0012
Version ۱.۲
١٢٣
<a href="page-5">Item ۵</a><code>123</code>
```

هر متد، ارقام دو گروه دیگر را به گروه مقصد تبدیل می‌کند. خروجی string است؛ عدد بودن ورودی را بررسی نمی‌کند، جداکننده هزارگان یا اعشار را تغییر نمی‌دهد و محاسبه‌ای انجام نمی‌دهد. برای نمایش عدد [راهنمای formatting](formatting.md) را ببینید. قبل از cast به عدد، ورودی را بررسی کنید. شناسه‌ای را که صفر اول دارد به integer تبدیل نکنید.

`convertContent()` فقط به ارقام فارسی تبدیل می‌کند. تگ، attribute، entity، کامنت و محتوای `script`، `style`، `pre`، `code` و `textarea` را تغییر نمی‌دهد. این متد sanitizer نیست. در مقابل، `toPersian()` روی کل string کار می‌کند و می‌تواند عدد داخل URL و attribute را هم تغییر دهد.

مطالب مرتبط: [متن فارسی](persian-text.md)، [پول](currency.md)، [اعتبارسنجی](validation.md).
