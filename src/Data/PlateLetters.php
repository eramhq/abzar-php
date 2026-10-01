<?php

declare(strict_types=1);

/**
 * Iranian car-plate middle letter → {@see \Eram\Abzar\Validation\PlateType} value.
 * Source: persian-tools numberplate dataset (src/modules/numberplate/codes.skip.ts
 * @ 25a2dc9f). ت (taxi) is absent upstream and added from the NAJA category list.
 * Keys use Persian ی / ک; callers fold Arabic ي / ك before lookup.
 * @see \Eram\Abzar\Data\DataSources
 */
return [
    'الف' => 'government',   // دولتی
    'ب'   => 'private',
    'پ'   => 'police',       // پلیس
    'ت'   => 'taxi',         // تاکسی
    'ث'   => 'military',     // سپاه
    'ج'   => 'private',
    'د'   => 'private',
    'ز'   => 'military',     // وزارت دفاع
    'ژ'   => 'disabled',     // معلولان و جانبازان
    'س'   => 'private',
    'ش'   => 'military',     // ارتش
    'ص'   => 'private',
    'ط'   => 'private',
    'ع'   => 'public',       // حمل و نقل عمومی
    'ف'   => 'military',     // ستاد کل نیروهای مسلح
    'ق'   => 'private',
    'ک'   => 'agricultural', // کشاورزی
    'گ'   => 'temporary',    // گذر موقت
    'ل'   => 'private',
    'م'   => 'private',
    'ن'   => 'private',
    'و'   => 'private',
    'ه'   => 'private',
    'ی'   => 'private',
    'D'   => 'diplomatic',   // دیپلمات
    'S'   => 'diplomatic',   // سفارتخانه
];
