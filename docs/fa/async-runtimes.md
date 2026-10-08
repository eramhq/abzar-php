---
title: "استفاده در workerهای طولانی‌مدت"
description: "استفاده از ابزار در worker بدون نگه‌داشتن ورودی درخواست در state کتابخانه."
---

# استفاده در workerهای طولانی‌مدت

کد ابزار محاسبات هم‌زمان و جدول‌های داخلی دارد. cacheهای static شامل جدول lookup، نگاشت کلمه یا normalizer پیش‌فرض هستند و از داده کد ساخته می‌شوند. API عمومی برای تنظیم global یا cache ورودی درخواست وجود ندارد.

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\PhoneNumber;

foreach (['09121234567', 'invalid', '02188887777'] as $input) {
    echo PhoneNumber::tryFrom($input)?->value() ?? 'invalid';
    echo "\n";
}
```

```text
09121234567
invalid
02188887777
```

## محدودیت‌های اجرا

در کارهای معمول و پشت سر هم Octane یا RoadRunner، پیاده‌سازی فعلی به reset hook مخصوص ابزار نیاز ندارد. normalizer با تنظیمات دلخواه را صریح بسازید یا پاس دهید. `CharNormalizer` از نوع `final` است و نمی‌توان از آن ارث برد.

در Swoole و ReactPHP هم این فراخوانی‌ها کار CPU به صورت synchronous هستند؛ promise یا I/O غیرهم‌زمان ندارند. پردازش متن بزرگ worker را مشغول می‌کند. بررسی سورس به معنی تضمین کلی thread safety یا تست روی همه runtimeها نیست. نگه‌داشتن referenceها، objectهای افزونه‌های native و چرخه worker را طبق نیاز برنامه مدیریت کنید.

مطالب مرتبط: [فریم‌ورک‌ها](framework-integration.md)، [متن فارسی](persian-text.md)، [سازگاری API](api-stability.md).
