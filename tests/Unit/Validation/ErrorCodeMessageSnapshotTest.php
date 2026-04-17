<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\Validation\ErrorCode;
use PHPUnit\Framework\TestCase;

/**
 * Pins the Persian text returned by {@see ErrorCode::message()} to the exact
 * literal strings the validators emitted before the 0.3 refactor. Consumer
 * assertions against the pre-refactor Persian text must keep passing byte-for-byte.
 */
final class ErrorCodeMessageSnapshotTest extends TestCase
{
    /**
     * @return iterable<string, array{ErrorCode, string}>
     */
    public static function snapshots(): iterable
    {
        yield 'national-id empty'         => [ErrorCode::NATIONAL_ID_EMPTY,             'کد ملی نمی‌تواند خالی باشد'];
        yield 'national-id length'        => [ErrorCode::NATIONAL_ID_WRONG_LENGTH,      'کد ملی باید ۱۰ رقم باشد'];
        yield 'national-id same digits'   => [ErrorCode::NATIONAL_ID_ALL_SAME_DIGITS,   'کد ملی نامعتبر است'];
        yield 'national-id sequential'    => [ErrorCode::NATIONAL_ID_SEQUENTIAL_DIGITS, 'کد ملی نامعتبر است'];
        yield 'national-id middle zeros'  => [ErrorCode::NATIONAL_ID_MIDDLE_ZEROS,      'کد ملی نامعتبر است'];
        yield 'national-id checksum'      => [ErrorCode::NATIONAL_ID_INVALID_CHECKSUM,  'کد ملی نامعتبر است'];
        yield 'national-id truncated'     => [ErrorCode::NATIONAL_ID_LIKELY_TRUNCATED,  'کد ملی باید ۱۰ رقم باشد؛ ممکن است صفرهای ابتدایی حذف شده باشد'];

        yield 'card empty'                => [ErrorCode::CARD_NUMBER_EMPTY,             'شماره کارت نمی‌تواند خالی باشد'];
        yield 'card length'               => [ErrorCode::CARD_NUMBER_WRONG_LENGTH,      'شماره کارت باید ۱۶ رقم باشد'];
        yield 'card checksum'             => [ErrorCode::CARD_NUMBER_INVALID_CHECKSUM,  'شماره کارت نامعتبر است'];
        yield 'card unknown bin'          => [ErrorCode::CARD_NUMBER_UNKNOWN_BIN,       'بانک صادرکننده شناسایی نشد'];

        yield 'iban empty'                => [ErrorCode::IBAN_EMPTY,                    'شماره شبا نمی‌تواند خالی باشد'];
        yield 'iban missing prefix'       => [ErrorCode::IBAN_MISSING_PREFIX,           'شماره شبا باید با IR شروع شود'];
        yield 'iban wrong length'         => [ErrorCode::IBAN_WRONG_LENGTH,             'شماره شبا باید ۲۶ کاراکتر باشد (IR + ۲۴ رقم)'];
        yield 'iban checksum'             => [ErrorCode::IBAN_INVALID_CHECKSUM,         'شماره شبا نامعتبر است'];

        yield 'phone empty'               => [ErrorCode::PHONE_NUMBER_EMPTY,            'شماره تلفن نمی‌تواند خالی باشد'];
        yield 'phone format'              => [ErrorCode::PHONE_NUMBER_INVALID_FORMAT,   'شماره تلفن باید یک شماره موبایل یا تلفن ثابت ایرانی معتبر باشد'];
        yield 'phone unknown operator'    => [ErrorCode::PHONE_NUMBER_UNKNOWN_OPERATOR, 'اپراتور این شماره شناسایی نشد'];

        yield 'legal-id empty'            => [ErrorCode::LEGAL_ID_EMPTY,                'شناسه حقوقی نمی‌تواند خالی باشد'];
        yield 'legal-id length'           => [ErrorCode::LEGAL_ID_WRONG_LENGTH,         'شناسه حقوقی باید ۱۱ رقم باشد'];
        yield 'legal-id middle zeros'     => [ErrorCode::LEGAL_ID_MIDDLE_ZEROS,         'شناسه حقوقی نامعتبر است'];
        yield 'legal-id checksum'         => [ErrorCode::LEGAL_ID_INVALID_CHECKSUM,     'شناسه حقوقی نامعتبر است'];

        yield 'number formatter invalid'  => [ErrorCode::NUMBER_FORMATTER_INVALID,      'مقدار ورودی عددی معتبر نیست'];
        yield 'number precision loss'     => [ErrorCode::NUMBER_TO_WORDS_PRECISION_LOSS, 'دقت عدد اعشاری از محدوده شناور PHP بیشتر است؛ مقدار را به‌صورت رشته ارسال کنید'];
        yield 'ordinal non positive'      => [ErrorCode::ORDINAL_NUMBER_NON_POSITIVE,   'عدد ترتیبی باید بزرگ‌تر از صفر باشد'];
        yield 'ordinal empty input'       => [ErrorCode::ORDINAL_NUMBER_EMPTY_INPUT,    'ورودی نمی‌تواند خالی باشد'];
        yield 'time ago invalid'          => [ErrorCode::TIME_AGO_INVALID_TIMESTAMP,    'تاریخ ورودی قابل تبدیل نیست'];

        yield 'plate empty'               => [ErrorCode::PLATE_NUMBER_EMPTY,            'شماره پلاک نمی‌تواند خالی باشد'];
        yield 'plate invalid format'      => [ErrorCode::PLATE_NUMBER_INVALID_FORMAT,   'قالب شماره پلاک نامعتبر است'];
        yield 'plate unknown letter'      => [ErrorCode::PLATE_NUMBER_UNKNOWN_LETTER,   'حرف میانی پلاک شناسایی نشد'];
        yield 'plate unknown city'        => [ErrorCode::PLATE_NUMBER_UNKNOWN_CITY_CODE, 'کد شهر پلاک شناسایی نشد'];

        yield 'env missing ext-intl'      => [ErrorCode::ENV_MISSING_EXT_INTL,          'این قابلیت به افزونهٔ ext-intl نیاز دارد'];
    }

    /**
     * @dataProvider snapshots
     */
    public function test_message(ErrorCode $code, string $expected): void
    {
        self::assertSame($expected, $code->message());
    }

    public function test_every_case_has_a_message(): void
    {
        foreach (ErrorCode::cases() as $case) {
            self::assertNotSame('', $case->message(), $case->name . ' returned empty message');
        }
    }
}
