---
title: "کدهای خطا"
description: "کدهای ErrorCode و پیام‌های دقیق فارسی کتابخانه."
---

# کدهای خطا

برای شرط برنامه از case یا مقدار `Eram\Abzar\Validation\ErrorCode` استفاده کنید؛ متن پیام ممکن است تغییر کند. مقدارهایی مثل `NATIONAL_ID.EMPTY` بخشی از API هستند و حذف یا تغییر نام آن‌ها تغییر ناسازگار محسوب می‌شود. [سیاست سازگاری](api-stability.md) را ببینید.

`$code->message()` پیام فارسی جدول را می‌دهد. بعضی کدها عمدا پیام یکسان دارند، ولی برنامه می‌تواند دلیل‌ها را از هم جدا کند.

## نوع نتیجه

- خطا در `errorCodes()` است؛ `isValid()` برابر `false` می‌شود.
- هشدار در `warningCodes()` است؛ ورودی معتبر است ولی lookup کامل نیست. `isStrictlyValid()` برابر `false` است. مقدار lookup معمولا `null` است، جز حرف ناشناخته پلاک که `PlateType::OTHER` می‌دهد.
- exception از `AbzarException` ارث می‌برد و کد آن با `errorCode()` خوانده می‌شود. در `ValidationException`، `result()` همه خطاها را می‌دهد.

```php
use Eram\Abzar\Validation\ErrorCode;
use Eram\Abzar\Validation\NationalId;

$r = NationalId::validate('1234567890');
$r->errorCodes();              // [ErrorCode::NATIONAL_ID_INVALID_CHECKSUM]
$r->errorCodes()[0]->value;    // 'NATIONAL_ID.INVALID_CHECKSUM'
$r->errorCodes()[0]->message(); // 'کد ملی نامعتبر است'

```

نمونه کامل مدیریت exception در [خطاها و هشدارها](error-handling.md) آمده است.

## اعتبارسنج‌ها

| کد | پیام دقیق کتابخانه | API | نوع |
|---|---|---|---|
| `NATIONAL_ID.EMPTY` | کد ملی نمی‌تواند خالی باشد | `NationalId` | خطا |
| `NATIONAL_ID.WRONG_LENGTH` | کد ملی باید ۱۰ رقم باشد | `NationalId` | خطا |
| `NATIONAL_ID.LIKELY_TRUNCATED` | کد ملی باید ۱۰ رقم باشد؛ ممکن است صفرهای ابتدایی حذف شده باشد | `NationalId` | خطا |
| `NATIONAL_ID.ALL_SAME_DIGITS` | کد ملی نامعتبر است | `NationalId` | خطا |
| `NATIONAL_ID.SEQUENTIAL_DIGITS` | کد ملی نامعتبر است | `NationalId` | خطا |
| `NATIONAL_ID.MIDDLE_ZEROS` | کد ملی نامعتبر است | `NationalId` | خطا |
| `NATIONAL_ID.INVALID_CHECKSUM` | کد ملی نامعتبر است | `NationalId` | خطا |
| `NATIONAL_ID.UNKNOWN_CITY_CODE` | محل صدور کد ملی شناسایی نشد | `NationalId` | هشدار |
| `CARD_NUMBER.EMPTY` | شماره کارت نمی‌تواند خالی باشد | `CardNumber` | خطا |
| `CARD_NUMBER.WRONG_LENGTH` | شماره کارت باید ۱۶ رقم باشد | `CardNumber` | خطا |
| `CARD_NUMBER.INVALID_CHECKSUM` | شماره کارت نامعتبر است | `CardNumber` | خطا |
| `CARD_NUMBER.ALL_SAME_DIGITS` | شماره کارت نامعتبر است | `CardNumber` | خطا |
| `CARD_NUMBER.UNKNOWN_BIN` | بانک صادرکننده شناسایی نشد | `CardNumber` | هشدار |
| `IBAN.EMPTY` | شماره شبا نمی‌تواند خالی باشد | `Iban` | خطا |
| `IBAN.MISSING_PREFIX` | شماره شبا باید با IR شروع شود | `Iban` | خطا |
| `IBAN.WRONG_LENGTH` | شماره شبا باید ۲۶ کاراکتر باشد (IR + ۲۴ رقم) | `Iban` | خطا |
| `IBAN.INVALID_CHECKSUM` | شماره شبا نامعتبر است | `Iban` | خطا |
| `IBAN.UNKNOWN_BANK` | بانک این شماره شبا شناسایی نشد | `Iban` | هشدار |
| `PHONE_NUMBER.EMPTY` | شماره تلفن نمی‌تواند خالی باشد | `PhoneNumber` | خطا |
| `PHONE_NUMBER.INVALID_FORMAT` | شماره تلفن باید یک شماره موبایل یا تلفن ثابت ایرانی معتبر باشد | `PhoneNumber` | خطا |
| `PHONE_NUMBER.UNKNOWN_OPERATOR` | اپراتور این شماره شناسایی نشد | `PhoneNumber` | هشدار |
| `PHONE_NUMBER.UNKNOWN_AREA_CODE` | پیش‌شماره این تلفن ثابت شناسایی نشد | `PhoneNumber` | هشدار |
| `LEGAL_ID.EMPTY` | شناسه حقوقی نمی‌تواند خالی باشد | `LegalId` | خطا |
| `LEGAL_ID.WRONG_LENGTH` | شناسه حقوقی باید ۱۱ رقم باشد | `LegalId` | خطا |
| `LEGAL_ID.MIDDLE_ZEROS` | شناسه حقوقی نامعتبر است | `LegalId` | خطا |
| `LEGAL_ID.INVALID_CHECKSUM` | شناسه حقوقی نامعتبر است | `LegalId` | خطا |
| `POSTAL_CODE.EMPTY` | کد پستی نمی‌تواند خالی باشد | `PostalCode` | خطا |
| `POSTAL_CODE.WRONG_LENGTH` | کد پستی باید ۱۰ رقم باشد | `PostalCode` | خطا |
| `POSTAL_CODE.INVALID_PATTERN` | کد پستی نامعتبر است | `PostalCode` | خطا |
| `BILL_ID.EMPTY` | شناسه قبض نمی‌تواند خالی باشد | `BillId` | خطا |
| `BILL_ID.WRONG_LENGTH` | شناسه قبض باید بین ۶ تا ۱۳ رقم باشد | `BillId` | خطا |
| `BILL_ID.INVALID_CHECKSUM` | شناسه قبض نامعتبر است | `BillId` | خطا |
| `BILL_ID.PAYMENT_MISMATCH` | شناسه پرداخت با شناسه قبض مطابقت ندارد | `BillId` | خطا |
| `BILL_ID.PAYMENT_EMPTY` | شناسه پرداخت نمی‌تواند خالی باشد | `BillId` | خطا |
| `BILL_ID.PAYMENT_WRONG_LENGTH` | شناسه پرداخت باید حداقل ۶ رقم باشد | `BillId` | خطا |
| `PLATE_NUMBER.EMPTY` | شماره پلاک نمی‌تواند خالی باشد | `PlateNumber` | خطا |
| `PLATE_NUMBER.INVALID_FORMAT` | قالب شماره پلاک نامعتبر است | `PlateNumber` | خطا |
| `PLATE_NUMBER.UNKNOWN_LETTER` | حرف میانی پلاک شناسایی نشد | `PlateNumber` | هشدار |
| `PLATE_NUMBER.UNKNOWN_CITY_CODE` | کد شهر پلاک شناسایی نشد | `PlateNumber` | هشدار |

شرایط هر خطا در راهنمای [کد ملی](national-id.md)، [کارت](card-number.md)، [شبا](iban.md)، [تلفن](phone-number.md)، [شناسه حقوقی](legal-id.md)، [کد پستی](postal-code.md)، [قبض](bill-id.md) و [پلاک](plate-number.md) توضیح داده شده است.

## formatterها، پول و محیط اجرا

| کد | پیام دقیق کتابخانه | API | نوع |
|---|---|---|---|
| `NUMBER_FORMATTER.INVALID_FORMAT` | مقدار ورودی عددی معتبر نیست | `NumberFormatter::withSeparators()`, `Currency::format()` | exception: `FormatException` |
| `NUMBER_TO_WORDS.OUT_OF_RANGE` | مقدار ورودی برای تبدیل به حروف خارج از محدوده پشتیبانی شده است | `NumberToWords::convert()` | exception: `FormatException` |
| `NUMBER_TO_WORDS.PRECISION_LOSS` | دقت عدد اعشاری از محدوده شناور PHP بیشتر است؛ مقدار را به‌صورت رشته ارسال کنید | `NumberToWords::convert()` | exception: `FormatException` |
| `ORDINAL_NUMBER.NON_POSITIVE` | عدد ترتیبی باید بزرگ‌تر از صفر باشد | `OrdinalNumber::toWord()`, `toShort()` | exception: `FormatException` |
| `ORDINAL_NUMBER.EMPTY_INPUT` | ورودی نمی‌تواند خالی باشد | `OrdinalNumber::addSuffix()` | exception: `FormatException` |
| `TIME_AGO.INVALID_TIMESTAMP` | تاریخ ورودی قابل تبدیل نیست | `TimeAgo` | exception: `FormatException` |
| `AMOUNT.NEGATIVE` | مبلغ نمی‌تواند منفی باشد | `Amount` | exception: `MoneyException` |
| `AMOUNT.OVERFLOW` | مبلغ از حداکثر مقدار قابل نمایش بیشتر است | `Amount` | exception: `MoneyException` |
| `HTML.SEGMENTATION_FAILED` | پردازش متن HTML ناموفق بود | `DigitConverter::convertContent()`, `CharNormalizer::normalizeContent()` | exception: `FormatException` |
| `VALIDATION.FAILED` | اعتبارسنجی ناموفق بود | `ValidationException::fromResult()` | exception: `ValidationException` |
| `FAKE.INVALID_ARGUMENT` | آرگومان ورودی برای تولید داده آزمایشی نامعتبر است | متدهای `::fake()` | exception: `ValidationException` |
| `ENV.MISSING_EXT_INTL` | این قابلیت به افزونهٔ ext-intl نیاز دارد | `PersianCollator`, `CharNormalizer(normalizeToNfc: true)` | exception: `EnvironmentException` |

`VALIDATION.FAILED` کد جایگزین است. `ValidationException::fromResult()` وقتی نتیجه کدی ندارد، مثلا از خطاهای string ساخته شده است، از آن استفاده می‌کند.


مطالب مرتبط: [مدیریت خطا](error-handling.md)، [اعتبارسنجی](validation.md).

پیام `NUMBER_TO_WORDS.PRECISION_LOSS` عینا از کتابخانه آمده، اما `NumberToWords::convert()` فقط `int|float` می‌گیرد. دادن string فارسی یا decimal با دقت دلخواه راه‌حل پشتیبانی‌شده نیست. از integer در محدوده مجاز یا روش جداگانه برای اعشار استفاده کنید. [نمایش عدد](formatting.md) را ببینید.
