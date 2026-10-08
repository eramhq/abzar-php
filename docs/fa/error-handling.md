---
title: "خطاها و هشدارها"
description: "مدیریت ورودی نامعتبر، هشدارهای lookup و exceptionهای کتابخانه."
---

# خطاها و هشدارها

برای خطای قابل انتظار در فرم از نتیجه اعتبارسنجی استفاده کنید. شرط‌ها را بر اساس کد خطا بنویسید و عملیات دارای exception را با نوع مناسب مدیریت کنید.

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Exception\ValidationException;
use Eram\Abzar\Validation\CardNumber;
use Eram\Abzar\Validation\NationalId;

$result = CardNumber::validate('1234567890123452');
echo $result->isValid() ? "accepted\n" : "rejected\n";
echo $result->warningCodes()[0]->value, "\n";

try {
    NationalId::from('1234567890');
} catch (ValidationException $e) {
    echo $e->errorCode()->value, "\n";
    echo $e->result()->errors()[0], "\n";
}
```

```text
accepted
CARD_NUMBER.UNKNOWN_BIN
NATIONAL_ID.INVALID_CHECKSUM
کد ملی نامعتبر است
```

## چه چیزی را بررسی کنیم؟

- `errors()` و `errorCodes()`: ورودی نامعتبر است. قبل از خواندن `detail()` نتیجه را بررسی کنید؛ نتیجه نامعتبر معمولا جزئیات ندارد.
- `warnings()` و `warningCodes()`: ورودی معتبر است، اما lookup کامل نشده. `from()` و `tryFrom()` آن را می‌پذیرند. اگر قانون برنامه شما وجود اطلاعات lookup را لازم می‌داند، از `isStrictlyValid()` استفاده کنید.
- `ValidationException`: ورودی نامعتبر در `from()` یا آرگومان نامعتبر در ساخت داده آزمایشی. `result()` نتیجه کامل را می‌دهد.
- `FormatException`: ورودی نامعتبر formatter یا شکست پردازش بخش‌های HTML.
- `MoneyException`: مقدار منفی یا سرریز در عملیات `Amount`.
- `EnvironmentException`: نبودن `intl` برای NFC یا مرتب‌سازی.

هر چهار exception از `Eram\Abzar\Exception\AbzarException` ارث می‌برند و `errorCode()` دارند. خطاهای خود PHP مثل نوع آرگومان اشتباه ممکن است `TypeError` ایجاد کنند؛ کلاس پایه همه خطاهای PHP را نمی‌گیرد.

متن فارسی خطا را با string مقایسه نکنید؛ ممکن است تغییر کند. از caseهای enum یا مقدار آن‌ها استفاده کنید. `WordsToNumber::parse()` برای متن غیرقابل پردازش `null` می‌دهد. هنگام ثبت لاگ، به وجود شناسه یا بخشی از ورودی در پیام exception توجه کنید.

مطالب مرتبط: [کدهای خطا](error-codes.md)، [اعتبارسنجی](validation.md)، [سازگاری API](api-stability.md).
