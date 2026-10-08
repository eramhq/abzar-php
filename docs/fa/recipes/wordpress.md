---
title: "استفاده در WordPress"
description: "hookهای اسلاگ و محتوا، فیلد REST و آماده‌سازی query جستجو."
---

# استفاده در WordPress

نمونه‌ها را در پلاگین خودتان و بعد از بارگذاری Composer autoloader قرار دهید. ابزار hook آماده نصب نمی‌کند. برای پروژه جداگانه persian-kit، [پروژه‌های مرتبط](../related.md) را ببینید.

## اسلاگ

```php
add_filter('sanitize_title', function (string $title, string $raw_title, string $context): string {
    if ($context !== 'save') {
        return $title;
    }

    return \Eram\Abzar\Text\Slug::generate($raw_title);
}, 10, 3);
```

## ارقام در محتوا

```php
add_filter('the_content', function (string $content): string {
    return \Eram\Abzar\Digits\DigitConverter::convertContent($content);
}, 20);
```

`convertContent()` تگ، attribute، entity، کامنت و محتوای script، style و بخش‌های code را تغییر نمی‌دهد.

## فیلد REST

فیلد را روی `rest_api_init` ثبت کنید:

```php
add_action('rest_api_init', function (): void {
    register_rest_field('post', 'customer_national_id', [
        'update_callback' => function ($value, \WP_Post $post) {
            if (!is_string($value)) {
                return new \WP_Error('invalid_nid', 'National ID must be a string', ['status' => 400]);
            }
            $ni = \Eram\Abzar\Validation\NationalId::tryFrom($value);
            if ($ni === null) {
                return new \WP_Error('invalid_nid', 'Invalid national ID', ['status' => 400]);
            }
            update_post_meta($post->ID, 'customer_national_id', $ni->value());
            update_post_meta($post->ID, 'customer_city', $ni->city());
            return true;
        },
    ]);
});
```

این callback فقط update را پیاده می‌کند و schema یا read callback ندارد. از کنترل دسترسی endpoint موجود post استفاده می‌کند؛ قبل از استفاده، آن را با سیاست دسترسی داده برنامه هماهنگ کنید.

## query جستجو

```php
add_action('pre_get_posts', function (\WP_Query $q): void {
    if (!$q->is_search() || !$q->is_main_query()) {
        return;
    }
    $s = $q->get('s');
    if (is_string($s) && $s !== '') {
        $normalizer = new \Eram\Abzar\Text\CharNormalizer();
        $q->set('s', $normalizer->normalizeForSearch($s));
    }
});
```

## خروجی

بعد از ثبت hookها در WordPress:

```php
echo apply_filters('sanitize_title', '', 'سلام دنیا', 'save'), "\n";
echo \Eram\Abzar\Digits\DigitConverter::convertContent('<p>Item 5</p>'), "\n";
```

```text
سلام-دنیا
<p>Item ۵</p>
```

خروجی اسلاگ با فرض نبود فیلتر بعدی که آن را تغییر دهد است. فیلترهای دیگر `the_content` هم می‌توانند HTML نهایی را تغییر دهند.

یکسان‌سازی query به‌تنهایی نوشته‌های قبلی را تغییر نمی‌دهد و index جستجو نمی‌سازد. تنظیمات یکسان را روی داده قابل جستجو هم اعمال کنید و collation دیتابیس را بررسی کنید. تبدیل HTML جای sanitizer را نمی‌گیرد و اسلاگ هم لزوما یکتا نیست.

مرجع فریم‌ورک: [REST fields](https://developer.wordpress.org/reference/functions/register_rest_field/) و [pre_get_posts](https://developer.wordpress.org/reference/hooks/pre_get_posts/).

مطالب مرتبط: [فریم‌ورک‌ها](../framework-integration.md)، [متن فارسی](../persian-text.md)، [ارقام](../digits.md).
