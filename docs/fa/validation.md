---
title: "اعتبارسنجی"
description: "انتخاب بین نتیجه و value object و تفاوت اعتبارسنجی با تایید هویت."
---

# اعتبارسنجی

از validatorها برای بررسی ورودی فرم یا API و یکسان‌سازی شناسه‌های ایرانی استفاده کنید. شناسه را به صورت string نگه دارید تا صفر اول آن حذف نشود. ارقام فارسی، عربی و انگلیسی پذیرفته می‌شوند.

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\CardNumber;

$result = CardNumber::validate('1234567890123452');
var_dump($result->isValid(), $result->isStrictlyValid());
echo $result->warningCodes()[0]->value, "\n";
var_dump(CardNumber::from('1234567890123452')->bank());
```

```text
bool(true)
bool(false)
CARD_NUMBER.UNKNOWN_BIN
NULL
```

این کارت از نظر checksum معتبر است، اما BIN آن در جدول بانک‌ها نیست. به همین دلیل `isValid()` برابر `true` و `isStrictlyValid()` برابر `false` است.

## انتخاب متد

| متد | ورودی معتبر | ورودی نامعتبر |
|---|---|---|
| `validate($input)` | `ValidationResult`، گاهی همراه هشدار | نتیجه همراه خطا |
| `from($input)` | value object تغییرناپذیر | `ValidationException` |
| `tryFrom($input)` | value object | `null` |

در `BillId` امضا متفاوت است: `validate($billId)` یک فیلد را بررسی می‌کند، ولی `from($billId, $paymentId)`، `tryFrom($billId, $paymentId)` و `validatePair()` هر دو شناسه را لازم دارند.

هشدار یعنی lookup اطلاعاتی پیدا نکرده است. هشدار مانع ساخت object یا استخراج از متن نمی‌شود. `isStrictlyValid()` معتبر بودن و نداشتن هشدار را بررسی می‌کند؛ باز هم استعلام رسمی نیست. برای حرف ناشناخته پلاک، نوع `PlateType::OTHER` برمی‌گردد؛ بقیه lookupهای ناموفق معمولا `null` هستند.

## راهنماها

- [کد ملی](national-id.md) و [شناسه حقوقی](legal-id.md): طول و checksum.
- [شماره کارت](card-number.md) و [شبا](iban.md): checksum و بانک صادرکننده.
- [تلفن](phone-number.md): ساختار و پیش‌شماره، بدون checksum.
- [کد پستی](postal-code.md): الگوی عدد، بدون بانک اطلاعات آدرس یا checksum.
- [قبض](bill-id.md): checksum قبض و ارتباط آن با شناسه پرداخت.
- [پلاک](plate-number.md): ساختار، نوع و استان، بدون استعلام خودرو.

## اشتباه‌های رایج

یک مقدار معتبر ممکن است به کسی اختصاص داده نشده باشد، غیرفعال باشد یا متعلق به شخص دیگری باشد. OTP، تایید هویت، مالکیت و وضعیت پرداخت به سرویس جداگانه نیاز دارند. پیش‌شماره موبایل بعد از ترابرد لزوما اپراتور فعلی را نشان نمی‌دهد. شهر صدور کد ملی هم آدرس فعلی فرد نیست.

خروجی `fake()` داده آزمایشی با ساختار معتبر است، اما ممکن است با یک شناسه واقعی یکسان باشد. `extractAll()` فقط در NationalId، CardNumber، Iban، PhoneNumber، PostalCode و PlateNumber وجود دارد. عددی که از متن استخراج می‌شود لزوما همان مفهوم مورد نظر شما را ندارد.

مطالب مرتبط: [خطاها و هشدارها](error-handling.md)، [کدهای خطا](error-codes.md)، [فریم‌ورک‌ها](framework-integration.md).
