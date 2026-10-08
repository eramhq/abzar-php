---
title: "شناسه قبض و پرداخت"
description: "بررسی checksum قبض و ارتباط آن با شناسه پرداخت."
---

# شناسه قبض و پرداخت

`BillId` شناسه قبض را به‌تنهایی یا همراه شناسه پرداخت بررسی می‌کند.

## نمونه قابل اجرا

کد را کنار `vendor/` ذخیره و اجرا کنید:

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\BillId;

$bill = BillId::from('7748317800142', '1770160');
echo $bill->type()->value, "\n";
var_dump(BillId::validatePair('7748317800142', '1770199')->isValid());
```

```text
phone
bool(false)
```

## قواعد و نکته‌ها

`validate($billId)` یک فیلد را می‌گیرد و `detail()->paymentId` در نتیجه آن `null` است. `from($billId, $paymentId)` و `tryFrom($billId, $paymentId)` هر دو فیلد را لازم دارند. `validatePair()` همان بررسی جفت را بدون ساخت value object انجام می‌دهد.

شناسه قبض ۶ تا ۱۳ رقم دارد. رقم آخر checksum پیمانه ۱۱ و رقم قبل از آن نوع قبض است. شناسه پرداخت ۶ تا ۱۸ رقم دارد؛ دو رقم آخر با ترکیب شناسه قبض و بخش اول شناسه پرداخت محاسبه می‌شوند. وزن‌های `[2, 3, 4, 5, 6, 7]` از راست تکرار می‌شوند.

| رقم نوع | مقدار `BillType` |
|---|---|
| 1 | `water` |
| 2 | `electric` |
| 3 | `gas` |
| 4 | `phone` |
| 5 | `mobile` |
| 6 | `tax` |
| 8 | `services` |
| 9 | `passport` |
| بقیه | `other` |

`billId()`، `paymentId()` و `type()` جزئیات را می‌دهند. `fake(BillType::ELECTRIC)` قبض آزمایشی و `fakePaymentId($billId)` پرداخت سازگار با آن می‌سازد.

این بررسی بدهی، مبلغ یا وضعیت پرداخت را از صادرکننده قبض نمی‌پرسد. هر دو شناسه را string نگه دارید و معتبر بودن checksum را با پرداخت موفق اشتباه نگیرید.

## کدهای خطا

کدهای همین بخش و پیام دقیق کتابخانه در [جدول کدهای خطا](error-codes.md) آمده است. برای شرط‌های برنامه از کد استفاده کنید، نه متن پیام.

مطالب مرتبط: [اعتبارسنجی](validation.md)، [مدیریت خطا](error-handling.md)، [اتصال به فریم‌ورک](framework-integration.md).
