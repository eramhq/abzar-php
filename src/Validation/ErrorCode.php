<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

/**
 * Stable, machine-readable error codes emitted by validators and format
 * exceptions. Values follow the {@code DOMAIN.REASON} convention and are
 * treated as API surface — renaming a case is a breaking change.
 *
 * Persian-language messages are paired with each case via {@see self::message()}.
 */
enum ErrorCode: string
{
    case NATIONAL_ID_EMPTY              = 'NATIONAL_ID.EMPTY';
    case NATIONAL_ID_WRONG_LENGTH       = 'NATIONAL_ID.WRONG_LENGTH';
    case NATIONAL_ID_LIKELY_TRUNCATED   = 'NATIONAL_ID.LIKELY_TRUNCATED';
    case NATIONAL_ID_ALL_SAME_DIGITS    = 'NATIONAL_ID.ALL_SAME_DIGITS';
    case NATIONAL_ID_SEQUENTIAL_DIGITS  = 'NATIONAL_ID.SEQUENTIAL_DIGITS';
    case NATIONAL_ID_MIDDLE_ZEROS       = 'NATIONAL_ID.MIDDLE_ZEROS';
    case NATIONAL_ID_INVALID_CHECKSUM   = 'NATIONAL_ID.INVALID_CHECKSUM';

    case CARD_NUMBER_EMPTY              = 'CARD_NUMBER.EMPTY';
    case CARD_NUMBER_WRONG_LENGTH       = 'CARD_NUMBER.WRONG_LENGTH';
    case CARD_NUMBER_INVALID_CHECKSUM   = 'CARD_NUMBER.INVALID_CHECKSUM';
    case CARD_NUMBER_UNKNOWN_BIN        = 'CARD_NUMBER.UNKNOWN_BIN';

    case IBAN_EMPTY                     = 'IBAN.EMPTY';
    case IBAN_MISSING_PREFIX            = 'IBAN.MISSING_PREFIX';
    case IBAN_WRONG_LENGTH              = 'IBAN.WRONG_LENGTH';
    case IBAN_INVALID_CHECKSUM          = 'IBAN.INVALID_CHECKSUM';

    case PHONE_NUMBER_EMPTY             = 'PHONE_NUMBER.EMPTY';
    case PHONE_NUMBER_INVALID_FORMAT    = 'PHONE_NUMBER.INVALID_FORMAT';
    case PHONE_NUMBER_UNKNOWN_OPERATOR  = 'PHONE_NUMBER.UNKNOWN_OPERATOR';

    case LEGAL_ID_EMPTY                 = 'LEGAL_ID.EMPTY';
    case LEGAL_ID_WRONG_LENGTH          = 'LEGAL_ID.WRONG_LENGTH';
    case LEGAL_ID_MIDDLE_ZEROS          = 'LEGAL_ID.MIDDLE_ZEROS';
    case LEGAL_ID_INVALID_CHECKSUM      = 'LEGAL_ID.INVALID_CHECKSUM';

    case POSTAL_CODE_EMPTY              = 'POSTAL_CODE.EMPTY';
    case POSTAL_CODE_WRONG_LENGTH       = 'POSTAL_CODE.WRONG_LENGTH';
    case POSTAL_CODE_INVALID_PATTERN    = 'POSTAL_CODE.INVALID_PATTERN';

    case BILL_ID_EMPTY                  = 'BILL_ID.EMPTY';
    case BILL_ID_WRONG_LENGTH           = 'BILL_ID.WRONG_LENGTH';
    case BILL_ID_INVALID_CHECKSUM       = 'BILL_ID.INVALID_CHECKSUM';
    case BILL_ID_PAYMENT_MISMATCH       = 'BILL_ID.PAYMENT_MISMATCH';
    case BILL_ID_PAYMENT_EMPTY          = 'BILL_ID.PAYMENT_EMPTY';
    case BILL_ID_PAYMENT_WRONG_LENGTH   = 'BILL_ID.PAYMENT_WRONG_LENGTH';

    case PLATE_NUMBER_EMPTY             = 'PLATE_NUMBER.EMPTY';
    case PLATE_NUMBER_INVALID_FORMAT    = 'PLATE_NUMBER.INVALID_FORMAT';
    case PLATE_NUMBER_UNKNOWN_LETTER    = 'PLATE_NUMBER.UNKNOWN_LETTER';
    case PLATE_NUMBER_UNKNOWN_CITY_CODE = 'PLATE_NUMBER.UNKNOWN_CITY_CODE';

    case NUMBER_FORMATTER_INVALID       = 'NUMBER_FORMATTER.INVALID_FORMAT';
    case NUMBER_TO_WORDS_OUT_OF_RANGE   = 'NUMBER_TO_WORDS.OUT_OF_RANGE';
    case NUMBER_TO_WORDS_PRECISION_LOSS = 'NUMBER_TO_WORDS.PRECISION_LOSS';
    case ORDINAL_NUMBER_NON_POSITIVE    = 'ORDINAL_NUMBER.NON_POSITIVE';
    case ORDINAL_NUMBER_EMPTY_INPUT     = 'ORDINAL_NUMBER.EMPTY_INPUT';
    case TIME_AGO_INVALID_TIMESTAMP     = 'TIME_AGO.INVALID_TIMESTAMP';

    case AMOUNT_NEGATIVE                = 'AMOUNT.NEGATIVE';

    case ENV_MISSING_EXT_INTL           = 'ENV.MISSING_EXT_INTL';

    public function message(): string
    {
        return match ($this) {
            self::NATIONAL_ID_EMPTY              => 'کد ملی نمی‌تواند خالی باشد',
            self::NATIONAL_ID_WRONG_LENGTH       => 'کد ملی باید ۱۰ رقم باشد',
            self::NATIONAL_ID_LIKELY_TRUNCATED   => 'کد ملی باید ۱۰ رقم باشد؛ ممکن است صفرهای ابتدایی حذف شده باشد',
            self::NATIONAL_ID_ALL_SAME_DIGITS,
            self::NATIONAL_ID_SEQUENTIAL_DIGITS,
            self::NATIONAL_ID_MIDDLE_ZEROS,
            self::NATIONAL_ID_INVALID_CHECKSUM   => 'کد ملی نامعتبر است',

            self::CARD_NUMBER_EMPTY              => 'شماره کارت نمی‌تواند خالی باشد',
            self::CARD_NUMBER_WRONG_LENGTH       => 'شماره کارت باید ۱۶ رقم باشد',
            self::CARD_NUMBER_INVALID_CHECKSUM   => 'شماره کارت نامعتبر است',
            self::CARD_NUMBER_UNKNOWN_BIN        => 'بانک صادرکننده شناسایی نشد',

            self::IBAN_EMPTY                     => 'شماره شبا نمی‌تواند خالی باشد',
            self::IBAN_MISSING_PREFIX            => 'شماره شبا باید با IR شروع شود',
            self::IBAN_WRONG_LENGTH              => 'شماره شبا باید ۲۶ کاراکتر باشد (IR + ۲۴ رقم)',
            self::IBAN_INVALID_CHECKSUM          => 'شماره شبا نامعتبر است',

            self::PHONE_NUMBER_EMPTY             => 'شماره تلفن نمی‌تواند خالی باشد',
            self::PHONE_NUMBER_INVALID_FORMAT    => 'شماره تلفن باید یک شماره موبایل یا تلفن ثابت ایرانی معتبر باشد',
            self::PHONE_NUMBER_UNKNOWN_OPERATOR  => 'اپراتور این شماره شناسایی نشد',

            self::LEGAL_ID_EMPTY                 => 'شناسه حقوقی نمی‌تواند خالی باشد',
            self::LEGAL_ID_WRONG_LENGTH          => 'شناسه حقوقی باید ۱۱ رقم باشد',
            self::LEGAL_ID_MIDDLE_ZEROS,
            self::LEGAL_ID_INVALID_CHECKSUM      => 'شناسه حقوقی نامعتبر است',

            self::POSTAL_CODE_EMPTY              => 'کد پستی نمی‌تواند خالی باشد',
            self::POSTAL_CODE_WRONG_LENGTH       => 'کد پستی باید ۱۰ رقم باشد',
            self::POSTAL_CODE_INVALID_PATTERN    => 'کد پستی نامعتبر است',

            self::BILL_ID_EMPTY                  => 'شناسه قبض نمی‌تواند خالی باشد',
            self::BILL_ID_WRONG_LENGTH           => 'شناسه قبض باید حداقل ۶ رقم باشد',
            self::BILL_ID_INVALID_CHECKSUM       => 'شناسه قبض نامعتبر است',
            self::BILL_ID_PAYMENT_MISMATCH       => 'شناسه پرداخت با شناسه قبض مطابقت ندارد',
            self::BILL_ID_PAYMENT_EMPTY          => 'شناسه پرداخت نمی‌تواند خالی باشد',
            self::BILL_ID_PAYMENT_WRONG_LENGTH   => 'شناسه پرداخت باید حداقل ۶ رقم باشد',

            self::PLATE_NUMBER_EMPTY             => 'شماره پلاک نمی‌تواند خالی باشد',
            self::PLATE_NUMBER_INVALID_FORMAT    => 'قالب شماره پلاک نامعتبر است',
            self::PLATE_NUMBER_UNKNOWN_LETTER    => 'حرف میانی پلاک شناسایی نشد',
            self::PLATE_NUMBER_UNKNOWN_CITY_CODE => 'کد شهر پلاک شناسایی نشد',

            self::NUMBER_FORMATTER_INVALID       => 'مقدار ورودی عددی معتبر نیست',
            self::NUMBER_TO_WORDS_OUT_OF_RANGE   => 'مقدار ورودی برای تبدیل به حروف خارج از محدوده پشتیبانی شده است',
            self::NUMBER_TO_WORDS_PRECISION_LOSS => 'دقت عدد اعشاری از محدوده شناور PHP بیشتر است؛ مقدار را به‌صورت رشته ارسال کنید',
            self::ORDINAL_NUMBER_NON_POSITIVE    => 'عدد ترتیبی باید بزرگ‌تر از صفر باشد',
            self::ORDINAL_NUMBER_EMPTY_INPUT     => 'ورودی نمی‌تواند خالی باشد',
            self::TIME_AGO_INVALID_TIMESTAMP     => 'تاریخ ورودی قابل تبدیل نیست',

            self::AMOUNT_NEGATIVE                => 'مبلغ نمی‌تواند منفی باشد',

            self::ENV_MISSING_EXT_INTL           => 'این قابلیت به افزونهٔ ext-intl نیاز دارد',
        };
    }
}
