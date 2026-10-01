<div dir="rtl">

# ابزار (abzar)

[English](README.md)

**ابزار** یک کتابخانهٔ PHP برای کار با داده‌های فارسی و ایرانی است. هیچ وابستگی زمان اجرا ندارد و روی PHP 8.1 به بالا کار می‌کند.

- **اعتبارسنجی:** کد ملی، شناسهٔ ملی اشخاص حقوقی، شماره کارت، شماره شبا، تلفن همراه و ثابت، کد پستی، شناسهٔ قبض و پرداخت، پلاک خودرو
- **پول:** تبدیل و نمایش تومان و ریال، و شیء `Amount` برای حساب‌وکتاب بدون خطای ضرب و تقسیم در ۱۰
- **قالب‌بندی:** عدد به حروف و حروف به عدد، اعداد ترتیبی، «۵ دقیقه پیش»، جداکنندهٔ هزارگان
- **متن:** یکسان‌سازی «ي» و «ك» عربی، نیم‌فاصله، تشخیص خط فارسی، اصلاح متنی که با صفحه‌کلید اشتباه تایپ شده، اسلاگ
- **ارقام:** تبدیل میان ارقام فارسی، عربی و انگلیسی

هر اعتبارسنج به‌جای `true` / `false` یک `ValidationResult` برمی‌گرداند. این نتیجه کد خطای پایدار (`ErrorCode`)، پیام فارسی و جزئیات ورودی را در خود دارد.

## نصب

<div dir="ltr">

```bash
composer require eram/abzar:^0.8@beta
```

</div>

به PHP 8.1 و افزونهٔ `mbstring` نیاز دارد. افزونهٔ `intl` تنها برای `PersianCollator` و یکسان‌سازی NFC لازم است.

## نمونه‌ها

### ۱. کد ملی

<div dir="ltr">

```php
use Eram\Abzar\Validation\NationalId;

NationalId::validate('۰۰۱۳۵۴۲۴۱۹')->isValid(); // true
NationalId::from('0013542419')->city();        // 'تهران مرکزی'
```

</div>

### ۲. شماره کارت و بانک صادرکننده

<div dir="ltr">

```php
use Eram\Abzar\Validation\CardNumber;

$card = CardNumber::from('6037-7016-8909-5443');
$card->bank();   // 'بانک کشاورزی'
$card->masked(); // '6037 70** **** 5443'
```

</div>

### ۳. شماره شبا

<div dir="ltr">

```php
use Eram\Abzar\Validation\Iban;

Iban::from('IR820540102680020817909002')->bank(); // 'بانک پارسیان'
```

</div>

### ۴. شماره تلفن

<div dir="ltr">

```php
use Eram\Abzar\Validation\PhoneNumber;

$phone = PhoneNumber::from('+98 912 123 4567');
$phone->value();    // '09121234567'
$phone->operator(); // 'همراه اول'
```

</div>

### ۵. خطاها با کد پایدار و پیام فارسی

<div dir="ltr">

```php
use Eram\Abzar\Validation\NationalId;

$r = NationalId::validate('1234567890');
$r->errorCodes()[0]->value;     // 'NATIONAL_ID.INVALID_CHECKSUM'
$r->errorCodes()[0]->message(); // 'کد ملی نامعتبر است'
```

</div>

### ۶. تومان و ریال

<div dir="ltr">

```php
use Eram\Abzar\Money\Amount;
use Eram\Abzar\Money\Currency;
use Eram\Abzar\Money\Unit;

$price = Amount::fromToman(50_000);
$price->inRials();                    // 500000
Currency::format($price);             // '۵۰،۰۰۰ تومان'
Currency::format($price, Unit::RIAL); // '۵۰۰،۰۰۰ ریال'
$price->toWords();                    // 'پنجاه هزار تومان'
```

</div>

### ۷. عدد به حروف و حروف به عدد

<div dir="ltr">

```php
use Eram\Abzar\Format\NumberToWords;
use Eram\Abzar\Format\OrdinalNumber;
use Eram\Abzar\Format\WordsToNumber;

NumberToWords::convert(1234);                         // 'یک هزار و دویست و سی و چهار'
OrdinalNumber::toWord(3);                             // 'سوم'
WordsToNumber::parse('یک هزار و دویست و سی و چهار'); // 1234
```

</div>

### ۸. ارقام

<div dir="ltr">

```php
use Eram\Abzar\Digits\DigitConverter;

DigitConverter::toPersian('Version 1.2'); // 'Version ۱.۲'
DigitConverter::toEnglish('نسخه ۱.۲');    // 'نسخه 1.2'
```

</div>

### ۹. یکسان‌سازی و نیم‌فاصله

<div dir="ltr">

```php
use Eram\Abzar\Text\CharNormalizer;
use Eram\Abzar\Text\HalfSpaceFixer;

(new CharNormalizer())->normalize('كتابي'); // 'کتابی'
HalfSpaceFixer::fix('می روم');              // 'می‌روم'
```

</div>

### ۱۰. اصلاح صفحه‌کلید و اسلاگ

<div dir="ltr">

```php
use Eram\Abzar\Text\KeyboardFixer;
use Eram\Abzar\Text\Slug;

KeyboardFixer::enToFa('sghl'); // 'سلام'
Slug::generate('سلام دنیا');   // 'سلام-دنیا'
```

</div>

## مستندات

مستندات کامل به زبان انگلیسی است:

- [README انگلیسی](README.md): همهٔ قابلیت‌ها با نمونه
- [فهرست مستندات](docs/en/README.md): صفحهٔ جداگانه برای هر اعتبارسنج، پول، و عدد به حروف
- [جدول کدهای خطا](docs/en/error-codes.md): همهٔ `ErrorCode`ها با پیام فارسی
- [راهنمای ارتقا](UPGRADE.md): تغییرات هر نسخه و نحوهٔ مهاجرت

ابزار هنوز در نسخهٔ `0.x` است و پیش از `1.0` ممکن است تغییر ناسازگار داشته باشد. برای تقویم شمسی از [`eram/daynum`](https://github.com/eramhq/daynum) استفاده کنید.

## مجوز

MIT. بخشی از جدول‌های داده از پروژهٔ [persian-tools](https://github.com/persian-tools/persian-tools) با مجوز MIT گرفته شده است.

</div>
