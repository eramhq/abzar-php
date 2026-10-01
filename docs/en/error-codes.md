# Error codes

Every failure abzar reports carries an `Eram\Abzar\Validation\ErrorCode`. The value (`NATIONAL_ID.EMPTY`) is stable API: renaming or removing a case is a breaking change (see the [API stability policy](api-stability.md)). Dispatch on the code, not on the message text.

`$code->message()` returns the Persian message shown below. Several codes share one message on purpose: an end user doesn't need to know *which* check failed, but your code can still tell them apart.

## Kinds

- **error**: in `ValidationResult::errorCodes()`. The input is invalid and `isValid()` is `false`.
- **warning**: in `ValidationResult::warningCodes()`. The input is valid, but a lookup (city, bank, operator, plate letter) found nothing, so that detail field is `null`. `isValid()` is `true` and `isStrictlyValid()` is `false`.
- **thrown**: carried by an exception. Every abzar exception extends `AbzarException`, so `catch (AbzarException $e)` and read `$e->errorCode()`.

A validator's `from()` throws `ValidationException` when `validate()` would return invalid. Its `errorCode()` is the first error code, and `result()` gives the full `ValidationResult`.

```php
use Eram\Abzar\Exception\ValidationException;
use Eram\Abzar\Validation\ErrorCode;
use Eram\Abzar\Validation\NationalId;

$r = NationalId::validate('1234567890');
$r->errorCodes();              // [ErrorCode::NATIONAL_ID_INVALID_CHECKSUM]
$r->errorCodes()[0]->value;    // 'NATIONAL_ID.INVALID_CHECKSUM'
$r->errorCodes()[0]->message(); // 'کد ملی نامعتبر است'

try {
    NationalId::from('1234567890');
} catch (ValidationException $e) {
    $e->errorCode();           // ErrorCode::NATIONAL_ID_INVALID_CHECKSUM
}
```

## Validators

| Code | Persian message | Emitted by | Kind |
|---|---|---|---|
| `NATIONAL_ID.EMPTY` | کد ملی نمی‌تواند خالی باشد | `NationalId` | error |
| `NATIONAL_ID.WRONG_LENGTH` | کد ملی باید ۱۰ رقم باشد | `NationalId` | error |
| `NATIONAL_ID.LIKELY_TRUNCATED` | کد ملی باید ۱۰ رقم باشد؛ ممکن است صفرهای ابتدایی حذف شده باشد | `NationalId` | error |
| `NATIONAL_ID.ALL_SAME_DIGITS` | کد ملی نامعتبر است | `NationalId` | error |
| `NATIONAL_ID.SEQUENTIAL_DIGITS` | کد ملی نامعتبر است | `NationalId` | error |
| `NATIONAL_ID.MIDDLE_ZEROS` | کد ملی نامعتبر است | `NationalId` | error |
| `NATIONAL_ID.INVALID_CHECKSUM` | کد ملی نامعتبر است | `NationalId` | error |
| `NATIONAL_ID.UNKNOWN_CITY_CODE` | محل صدور کد ملی شناسایی نشد | `NationalId` | warning |
| `CARD_NUMBER.EMPTY` | شماره کارت نمی‌تواند خالی باشد | `CardNumber` | error |
| `CARD_NUMBER.WRONG_LENGTH` | شماره کارت باید ۱۶ رقم باشد | `CardNumber` | error |
| `CARD_NUMBER.INVALID_CHECKSUM` | شماره کارت نامعتبر است | `CardNumber` | error |
| `CARD_NUMBER.ALL_SAME_DIGITS` | شماره کارت نامعتبر است | `CardNumber` | error |
| `CARD_NUMBER.UNKNOWN_BIN` | بانک صادرکننده شناسایی نشد | `CardNumber` | warning |
| `IBAN.EMPTY` | شماره شبا نمی‌تواند خالی باشد | `Iban` | error |
| `IBAN.MISSING_PREFIX` | شماره شبا باید با IR شروع شود | `Iban` | error |
| `IBAN.WRONG_LENGTH` | شماره شبا باید ۲۶ کاراکتر باشد (IR + ۲۴ رقم) | `Iban` | error |
| `IBAN.INVALID_CHECKSUM` | شماره شبا نامعتبر است | `Iban` | error |
| `IBAN.UNKNOWN_BANK` | بانک این شماره شبا شناسایی نشد | `Iban` | warning |
| `PHONE_NUMBER.EMPTY` | شماره تلفن نمی‌تواند خالی باشد | `PhoneNumber` | error |
| `PHONE_NUMBER.INVALID_FORMAT` | شماره تلفن باید یک شماره موبایل یا تلفن ثابت ایرانی معتبر باشد | `PhoneNumber` | error |
| `PHONE_NUMBER.UNKNOWN_OPERATOR` | اپراتور این شماره شناسایی نشد | `PhoneNumber` | warning |
| `PHONE_NUMBER.UNKNOWN_AREA_CODE` | پیش‌شماره این تلفن ثابت شناسایی نشد | `PhoneNumber` | warning |
| `LEGAL_ID.EMPTY` | شناسه حقوقی نمی‌تواند خالی باشد | `LegalId` | error |
| `LEGAL_ID.WRONG_LENGTH` | شناسه حقوقی باید ۱۱ رقم باشد | `LegalId` | error |
| `LEGAL_ID.MIDDLE_ZEROS` | شناسه حقوقی نامعتبر است | `LegalId` | error |
| `LEGAL_ID.INVALID_CHECKSUM` | شناسه حقوقی نامعتبر است | `LegalId` | error |
| `POSTAL_CODE.EMPTY` | کد پستی نمی‌تواند خالی باشد | `PostalCode` | error |
| `POSTAL_CODE.WRONG_LENGTH` | کد پستی باید ۱۰ رقم باشد | `PostalCode` | error |
| `POSTAL_CODE.INVALID_PATTERN` | کد پستی نامعتبر است | `PostalCode` | error |
| `BILL_ID.EMPTY` | شناسه قبض نمی‌تواند خالی باشد | `BillId` | error |
| `BILL_ID.WRONG_LENGTH` | شناسه قبض باید بین ۶ تا ۱۳ رقم باشد | `BillId` | error |
| `BILL_ID.INVALID_CHECKSUM` | شناسه قبض نامعتبر است | `BillId` | error |
| `BILL_ID.PAYMENT_MISMATCH` | شناسه پرداخت با شناسه قبض مطابقت ندارد | `BillId` | error |
| `BILL_ID.PAYMENT_EMPTY` | شناسه پرداخت نمی‌تواند خالی باشد | `BillId` | error |
| `BILL_ID.PAYMENT_WRONG_LENGTH` | شناسه پرداخت باید حداقل ۶ رقم باشد | `BillId` | error |
| `PLATE_NUMBER.EMPTY` | شماره پلاک نمی‌تواند خالی باشد | `PlateNumber` | error |
| `PLATE_NUMBER.INVALID_FORMAT` | قالب شماره پلاک نامعتبر است | `PlateNumber` | error |
| `PLATE_NUMBER.UNKNOWN_LETTER` | حرف میانی پلاک شناسایی نشد | `PlateNumber` | warning |
| `PLATE_NUMBER.UNKNOWN_CITY_CODE` | کد شهر پلاک شناسایی نشد | `PlateNumber` | warning |

Per-validator pages explain when each code fires: [National ID](national-id.md), [Card Number](card-number.md), [IBAN](iban.md), [Phone Number](phone-number.md), [Legal ID](legal-id.md), [Postal Code](postal-code.md), [Bill ID](bill-id.md), [Plate Number](plate-number.md).

## Formatters, money and environment

| Code | Persian message | Emitted by | Kind |
|---|---|---|---|
| `NUMBER_FORMATTER.INVALID_FORMAT` | مقدار ورودی عددی معتبر نیست | `NumberFormatter::withSeparators()`, `Currency::format()` | thrown: `FormatException` |
| `NUMBER_TO_WORDS.OUT_OF_RANGE` | مقدار ورودی برای تبدیل به حروف خارج از محدوده پشتیبانی شده است | `NumberToWords::convert()` | thrown: `FormatException` |
| `NUMBER_TO_WORDS.PRECISION_LOSS` | دقت عدد اعشاری از محدوده شناور PHP بیشتر است؛ مقدار را به‌صورت رشته ارسال کنید | `NumberToWords::convert()` | thrown: `FormatException` |
| `ORDINAL_NUMBER.NON_POSITIVE` | عدد ترتیبی باید بزرگ‌تر از صفر باشد | `OrdinalNumber::toWord()`, `toShort()` | thrown: `FormatException` |
| `ORDINAL_NUMBER.EMPTY_INPUT` | ورودی نمی‌تواند خالی باشد | `OrdinalNumber::addSuffix()` | thrown: `FormatException` |
| `TIME_AGO.INVALID_TIMESTAMP` | تاریخ ورودی قابل تبدیل نیست | `TimeAgo` | thrown: `FormatException` |
| `AMOUNT.NEGATIVE` | مبلغ نمی‌تواند منفی باشد | `Amount` | thrown: `MoneyException` |
| `AMOUNT.OVERFLOW` | مبلغ از حداکثر مقدار قابل نمایش بیشتر است | `Amount` | thrown: `MoneyException` |
| `HTML.SEGMENTATION_FAILED` | پردازش متن HTML ناموفق بود | `DigitConverter::convertContent()`, `CharNormalizer::normalizeContent()` | thrown: `FormatException` |
| `VALIDATION.FAILED` | اعتبارسنجی ناموفق بود | `ValidationException::fromResult()` | thrown: `ValidationException` |
| `FAKE.INVALID_ARGUMENT` | آرگومان ورودی برای تولید داده آزمایشی نامعتبر است | `::fake()` generators | thrown: `ValidationException` |
| `ENV.MISSING_EXT_INTL` | این قابلیت به افزونهٔ ext-intl نیاز دارد | `PersianCollator`, `CharNormalizer(normalizeToNfc: true)` | thrown: `EnvironmentException` |

`VALIDATION.FAILED` is a fallback. `ValidationException::fromResult()` uses it for a result that has no code, e.g. one built from plain-string errors.
