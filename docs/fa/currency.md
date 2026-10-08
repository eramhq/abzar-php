---
title: "تومان و ریال"
description: "محاسبه مبلغ با Amount و نمایش تومان و ریال بدون حذف باقی‌مانده ریالی."
---

# تومان و ریال

`Amount` مبلغ را به صورت تعداد صحیح ریال نگه می‌دارد. `Currency` آن را با واحد و ارقام دلخواه نمایش می‌دهد. این ترکیب از اشتباه ضرب و تقسیم در ۱۰ جلوگیری می‌کند.

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Money\{Amount, Currency, Unit};

$price = Amount::fromRials(12_345);
echo Currency::format($price), "\n";
echo $price->toWords(), "\n";
echo $price->inToman(), "\n";
echo $price->times(2)->inRials(), "\n";
echo Currency::format($price, Unit::RIAL), "\n";
```

```text
۱،۲۳۴.۵ تومان
یک هزار و دویست و سی و چهار تومان و پنج ریال
1234
24690
۱۲،۳۴۵ ریال
```

## نمایش و تبدیل

`Currency::format()` ورودی `int|float|string|Amount` می‌گیرد. مقدار پیش‌فرض `unit` برابر `Unit::TOMAN`، گزینه `persianDigits` برابر `true`، گزینه `withUnit` برابر `true` و `separator` برابر `،` است. string عددی می‌تواند ارقام فارسی یا عربی و جداکننده `،`، `٬` یا `,` داشته باشد.

برای ورودی scalar، واحد فقط برچسب نمایش است: `Currency::format(1234, Unit::RIAL)` عدد ۱۲۳۴ را ریال فرض می‌کند؛ از تومان تبدیل نمی‌کند. ولی `Amount` واحد داخلی مشخصی دارد و برای نمایش تبدیل می‌شود.

`Currency::convert($amount, $from, $to)` برای تومان به ریال ضرب در ۱۰ و برای ریال به تومان تقسیم بر ۱۰ انجام می‌دهد. اگر ورودی integer و تقسیم دقیق باشد خروجی integer است؛ در غیر این صورت float می‌شود. ورودی float همان float می‌ماند. این متد محدودیت مقدار منفی و محافظت از سرریز `Amount` را ندارد.

## محاسبه

```php
use Eram\Abzar\Money\Amount;

$subtotal = Amount::fromToman(120_000);
$vat = $subtotal->percentOf(9); // 108000 rials
$total = $subtotal->add($vat); // 1308000 rials
$total->times(3)->inRials(); // 3924000
$subtotal->inRials(); // 1200000
```

`Amount` تغییرناپذیر و غیرمنفی است. محاسبات object جدید می‌دهند؛ مقایسه‌ها boolean یا integer برمی‌گردانند.

| متد | نتیجه و نکته |
|---|---|
| `fromRials(int $rials)` | ساخت مبلغ ریالی؛ مقدار منفی خطا دارد |
| `fromToman(int $toman)` | ساخت مبلغ تومانی؛ منفی و سرریز خطا دارند |
| `inRials()` | کل مبلغ به صورت integer ریال |
| `inToman()` | بخش صحیح تومان؛ باقی‌مانده حذف می‌شود |
| `add(Amount)` | جمع با بررسی سرریز |
| `subtract(Amount)` | تفریق؛ نتیجه منفی خطا دارد |
| `times(int $qty)` | ضرب در تعداد غیرمنفی؛ صفر، مبلغ صفر می‌دهد |
| `percentOf(int\|float $pct, int $mode = PHP_ROUND_HALF_EVEN)` | درصد مبلغ، گرد شده به نزدیک‌ترین ریال |
| `equals(Amount)`, `isZero()` | مقایسه برابری و صفر بودن |
| `greaterThan(Amount)`, `lessThan(Amount)` | مقایسه بزرگ‌تر و کوچک‌تر |
| `greaterThanOrEqual(Amount)`, `lessThanOrEqual(Amount)` | مقایسه همراه برابری |
| `compareTo(Amount)` | مقدار `-1`، `0` یا `1`؛ مناسب `usort` |
| `toWords(Unit $unit = Unit::TOMAN)` | متن فارسی با واحد و باقی‌مانده ریالی |
| `jsonSerialize()` | آرایه با کلید `rials`؛ JSON نمونه `{"rials":12345}` |

## دقت و اشتباه‌های رایج

`Amount` کسری از یک ریال نگه نمی‌دارد. پنج ریال در ۱۲۳۴۵ ریال حفظ می‌شود و در نمایش تومان به صورت `.۵` یا «و پنج ریال» می‌آید. برای ذخیره دقیق از `inRials()` استفاده کنید؛ `inToman()` باقی‌مانده را حذف می‌کند. سازنده‌ها integer می‌گیرند، نه string فارسی. قبل از cast، ورودی دارای اعشار را صریح بررسی و رد کنید.

`percentOf()` به طور پیش‌فرض از `PHP_ROUND_HALF_EVEN` استفاده می‌کند. محاسبه میانی float است و برای مبلغ‌های بزرگ‌تر از حدود 2^53 ریال ممکن است دقت کم شود. این API محاسبات با دقت نامحدود نیست. سقف integer به `PHP_INT_MAX` محیط بستگی دارد.

عملیات منفی `AMOUNT.NEGATIVE` و سرریز یا درصد نامتناهی `AMOUNT.OVERFLOW` ایجاد می‌کنند؛ نوع exception برابر `MoneyException` است. `toWords()` برای مبلغ غیرمنفی معتبر، کل محدوده integer را پوشش می‌دهد. مثلا پنج ریال را «پنج ریال» و صفر را «صفر تومان» می‌نویسد.

مطالب مرتبط: [نمایش عدد](formatting.md)، [حروف به عدد](words-to-number.md)، [مدیریت خطا](error-handling.md).
