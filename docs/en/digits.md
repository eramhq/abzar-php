---
title: "Persian, Arabic and English digits"
description: "Convert digit scripts in strings and HTML without parsing a number."
---

# Persian, Arabic and English digits

Use `DigitConverter` for display or to normalize a string before passing it to a numeric API. Conversion preserves leading zeros and non-digit text.

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Digits\DigitConverter;

echo DigitConverter::toEnglish('۰۰۱٢'), "\n";
echo DigitConverter::toPersian('Version 1.2'), "\n";
echo DigitConverter::toArabic('۱۲3'), "\n";
echo DigitConverter::convertContent('<a href="page-5">Item 5</a><code>123</code>'), "\n";
```

```text
0012
Version ۱.۲
١٢٣
<a href="page-5">Item ۵</a><code>123</code>
```

Each target method converts both other supported digit scripts. These methods return strings; they do not validate a number, strip grouping, convert decimal separators, or perform arithmetic. Use [formatting](formatting.md) for numeric display and validate before casting to an integer. Never cast identifiers with leading zeros.

`convertContent()` converts to Persian only. It leaves tags, attributes, entities, comments and `script` / `style` / `pre` / `code` / `textarea` content untouched. It uses a regex segmenter, not a sanitizer; plain `toPersian()` would also change digits in URLs and attributes.

Related: [Persian text](persian-text.md), [money](currency.md), [validation](validation.md).
