---
title: "متن فارسی"
description: "یکسان‌سازی حروف، آماده‌سازی متن برای جستجو، اسلاگ و نیم‌فاصله."
---

# متن فارسی

`CharNormalizer` شکل‌های عربی حروف را با متن فارسی یکسان می‌کند. تنظیمات متن اصلی و کلید جستجو را متناسب با نیاز برنامه انتخاب کنید.

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Text\{CharNormalizer, HalfSpaceFixer, Slug};

$n = new CharNormalizer();
echo $n->normalize('كتابي ٠١٢'), "\n";
echo $n->normalizeForSearch('۱۲۳ كتاب'), "\n";
echo HalfSpaceFixer::fix('می روم'), "\n";
echo Slug::generate('محصول ۱۲۳'), "\n";
echo $n->normalizeContent('<p title="كتابي">كتابي</p>'), "\n";
```

```text
کتابی ۰۱۲
123 کتاب
می‌روم
محصول-123
<p title="كتابي">کتابی</p>
```

## تنظیمات

حالت پیش‌فرض `ي` و `ك` و ارقام عربی را تبدیل می‌کند؛ ارقام انگلیسی تغییری نمی‌کنند. گزینه‌های سازنده همگی پیش‌فرض `false` دارند: `tehMarbuta`، `foldHamza`، `stripTashkeel`، `stripKashida`، `stripBidiMarks` و `normalizeToNfc`. گزینه `foldHamza` حرف `آ` را حفظ می‌کند. NFC به `intl` نیاز دارد، بقیه گزینه‌ها نه.

`normalizeForSearch()` تنظیمات انتخاب‌شده را اعمال می‌کند و ارقام را به انگلیسی تبدیل می‌کند. همین تنظیمات را برای داده قابل جستجو و query به کار ببرید. این متد index نمی‌سازد، رتبه‌بندی و fuzzy search ندارد، ریشه کلمه را پیدا نمی‌کند و به‌تنهایی جستجوی دیتابیس را اصلاح نمی‌کند. نیم‌فاصله و فاصله‌های تکراری را هم حذف نمی‌کند.

## HTML و نیم‌فاصله

`normalizeContent()` فقط بخش‌های متنی را تغییر می‌دهد. تگ، attribute، entity، کامنت و محتوای `script`، `style`، `pre`، `code` و `textarea` دست‌نخورده می‌مانند. این روش بر پایه regex است؛ HTML sanitizer یا DOM parser کامل نیست. پاک‌سازی HTML نامطمئن را جداگانه انجام دهید.

`HalfSpaceFixer` چند قاعده پیشوند و پسوند دارد و دستور زبان را نمی‌فهمد. ممکن است کلمه بعد از `می` را حتی وقتی فعل نیست بچسباند. همه ترکیب‌ها یا فاصله‌ها را اصلاح نمی‌کند؛ تغییر گروهی متن را قبل از ذخیره بررسی کنید.

## مرتب‌سازی و تشخیص کاراکتر

```php
use Eram\Abzar\Text\{PersianCollator, Script};

(new PersianCollator())->sort(['ج', 'ب', 'ا']); // ['ا', 'ب', 'ج']; requires intl
Script::isPersian('سلام دنیا'); // true
Script::hasArabic('ك'); // true
```

بررسی‌های `Script` بر پایه مجموعه کاراکترها هستند، نه تشخیص زبان. خروجی `Slug::generate()` ممکن است خالی یا تکراری باشد؛ یکتا بودن و routing به برنامه مربوط است. ترتیب مرتب‌سازی به داده ICU نصب‌شده هم بستگی دارد.

مطالب مرتبط: [ارقام](digits.md)، [اصلاح کیبورد](keyboard-fixer.md)، [WordPress](recipes/wordpress.md)، [خطاها](error-handling.md).
