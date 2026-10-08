---
title: "تفاوت با persian-tools"
description: "پوشش نمونه‌های upstream و تفاوت‌های عمدی ابزار با کتابخانه JavaScript."
---

# تفاوت با persian-tools

تست‌های contract ابزار بخشی از نمونه‌های [persian-tools](https://github.com/persian-tools/persian-tools) را اجرا می‌کنند. این صفحه برای مهاجرت بین دو کتابخانه است، نه ادعای برابری کامل.

نمونه‌ها در [tests/fixtures/persian-tools](../../tests/fixtures/persian-tools/) با SHA مشخص [25a2dc9f](https://github.com/persian-tools/persian-tools/tree/25a2dc9f22444b78bf16f6c48bda6727688e8552) نگه‌داری می‌شوند. تست‌ها فایل و خط منبع را ثبت می‌کنند.

## پوشش


| فایل نمونه | API ابزار | کلاس تست |
|---|---|---|
| `verifyIranianNationalId.spec.ts` | `NationalId::validate()` | `PersianToolsContractTest` |
| `verifyCardNumber.spec.ts` | `CardNumber::validate()` | `PersianToolsContractTest` |
| `sheba.spec.ts` | `Iban::validate()`, `Iban::bank()` | `PersianToolsContractTest` |
| `phoneNumber.spec.ts` | `PhoneNumber::validate()`, `operatorEnum()` | `PersianToolsContractTest` |
| `findCapitalByProvince.spec.ts` | `Province::fromPersian()` | `PersianToolsContractTest` |
| `bill.spec.ts` | `BillId::validatePair()` | `PersianToolsContractTest` |
| `verifyIranianLegalId.spec.ts` | `LegalId::validate()` | `LegalIdContractTest` |
| `NumberToWords.spec.ts` | `NumberToWords::convert()`, `OrdinalNumber::toWord()` | `NumberWordsContractTest` |
| `addOrdinalSuffix.spec.ts` | `OrdinalNumber::addSuffix()` | `NumberWordsContractTest` |
| `wordsToNumber.spec.ts` | `WordsToNumber::parse()` | `NumberWordsContractTest` |
| `addCommas.spec.ts` | `NumberFormatter::withSeparators()` | `FormattingContractTest` |
| `removeCommas.spec.ts` | `NumberFormatter::withSeparators($s, '')` | `FormattingContractTest` |
| `digits.spec.ts` | `DigitConverter::toPersian()` / `toEnglish()` / `toArabic()` | `FormattingContractTest` |
| `isPersian.spec.ts` | `Script::isPersian()`, `Script::hasPersian()` | `TextContractTest` |
| `isArabic.spec.ts` | `Script::isArabic()` | `TextContractTest` |
| `toPersianChars.spec.ts` | `CharNormalizer::normalize()` | `TextContractTest` |
| `halfSpace.spec.ts` | `HalfSpaceFixer::fix()` | `TextContractTest` |



تست نوع ورودی JavaScript معادل جداگانه ندارد چون امضای متدهای PHP تایپ دارد.

## بخش‌های خارج از پوشش

- تست `slugify` در upstream غیرفعال است. ورودی `timeAgo` تاریخ شمسی است؛ ابزار parser تقویم شمسی ندارد.
- fuzzy parsing و `moneyWordsToNumber` در ابزار وجود ندارند.
- گزینه‌های خروجی `digits` و `addCommas` با `DigitConverter` و `NumberFormatter` انجام می‌شوند. برای یکسان‌سازی متن ورودی از `CharNormalizer` استفاده کنید.
- نمونه‌های `extractCardNumber`، `getBankNameFromCardNumber`، `getPlaceByIranNationalId`، `numberplate`، `findProvinceFromCoordinate`، `remainingTime`، `removeOrdinalSuffix`، `textAnalyzer` و `URLfix` هنوز به contract test منتقل نشده‌اند. وجود فایل fixture به معنی وجود API معادل نیست.

## اعتبارسنج‌ها

کارت `1234567890123452` در ابزار معتبر و دارای هشدار `CARD_NUMBER.UNKNOWN_BIN` است؛ upstream آن را نامعتبر می‌داند. شماره `09802002580` هم در ابزار با هشدار `PHONE_NUMBER.UNKNOWN_OPERATOR` پذیرفته می‌شود. این دو تفاوت در unit testهای validator ثبت شده‌اند. جدول پلاک از داده upstream با تفاوت‌های ثبت‌شده در [PlateCodes.php](../../src/Data/PlateCodes.php) آمده است.

## عدد و حروف

تفاوت‌های عمدی در registry تست‌ها با ورودی، خروجی هر دو کتابخانه و دلیل ثبت شده‌اند و تست هم خروجی ابزار و هم متفاوت ماندن آن را بررسی می‌کند.

ابزار صدگان را پیوسته می‌نویسد، مثل `چهارصد` و `نهصد`؛ صد تنها `یکصد` است و 10¹⁵ با `کوادریلیون` نوشته می‌شود. `WordsToNumber` شکل‌های جدا مثل `چهار صد`، `صد`، `کوآدریلیون`، `شیش`، `چارصد` و `بیلیون` را هم می‌خواند.

```php
use Eram\Abzar\Format\{NumberToWords, WordsToNumber};

NumberToWords::convert(500443); // 'پانصد هزار و چهارصد و چهل و سه'
WordsToNumber::parse('منفی ۳ هزار'); // null
WordsToNumber::parse('سلام دنیا'); // null
```

upstream در برخی حالت‌ها کلمه غیرعددی را کنار می‌گذارد، رقم و حروف را مخلوط می‌پذیرد یا برای متن بدون عدد صفر می‌دهد. ابزار به جای حدس `null` می‌دهد. عدد ترتیبی منفی پذیرفته نیست و `ORDINAL_NUMBER.NON_POSITIVE` دارد. پسوند `سی` به صورت `سی‌ام` نوشته می‌شود. float و متن آزاد را روش مطمئن انتقال مبلغ ندانید.

## نمایش و ارقام

در ابزار متد `removeCommas` وجود ندارد؛ `NumberFormatter::withSeparators($s, '')` string برمی‌گرداند و اعشار را نگه می‌دارد. `DigitConverter` بر اساس مقصد عمل می‌کند و هر دو گروه دیگر ارقام را تبدیل می‌کند، در حالی که upstream برای مبداهای مختلف متد جدا دارد.

## متن و نیم‌فاصله

`Script` و `CharNormalizer` با نمونه‌های منتقل‌شده سازگارند. `HalfSpaceFixer` قاعده‌های محدودتری دارد:

- `بزرگ تر` و `بزرگ ترین` را با نیم‌فاصله می‌نویسد.
- پیشوندهای `بی` و `هم` را خودکار نمی‌چسباند؛ تشخیص معنی به واژه‌نامه نیاز دارد.
- ترکیب‌هایی مثل `به هر حال` و `این جا` را به صورت ثابت اصلاح نمی‌کند.
- فاصله‌های اضافه، trim و فاصله قبل از نشانه‌گذاری را اصلاح نمی‌کند؛ فقط فاصله هدف قاعده عوض می‌شود.

## به‌روزرسانی نمونه‌ها

نگه‌دارندگان از `composer fixtures:pull` و SHA ثبت‌شده در `tools/fixtures/SHA` استفاده می‌کنند. تغییر pin باید همراه اجرای تست و بازبینی تفاوت‌ها باشد. جزئیات در [راهنمای fixtureها](../../tests/fixtures/persian-tools/README.md) است.

مطالب مرتبط: [حروف به عدد](words-to-number.md)، [نمایش عدد](formatting.md)، [متن فارسی](persian-text.md)، [اعتبارسنجی](validation.md).
