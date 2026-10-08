---
title: "Number and time formatting"
description: "Format grouped numbers, Persian words, ordinals and relative times."
---

# Number and time formatting

Use these formatters for human-readable UI values after validating the input.

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Format\{NumberFormatter, NumberToWords, OrdinalNumber, TimeAgo};

echo NumberFormatter::withSeparators('۱۲۳۴٫۵۰'), "\n";
echo NumberToWords::convert(1234), "\n";
echo NumberToWords::convert(3.25), "\n";
echo OrdinalNumber::toWord(3), "\n";
echo OrdinalNumber::toShort(43), "\n";
echo TimeAgo::format(1_700_000_000, now: 1_700_000_300), "\n";
```

```text
1,234.50
یک هزار و دویست و سی و چهار
سه ممیز بیست و پنج
سوم
۴۳ام
۵ دقیقه پیش
```

## Numeric input

`NumberFormatter::withSeparators()` accepts `int|float|string`, normalizes Persian/Arabic digits, grouping and `٫`, and returns an ASCII-digit string. It accepts plain decimals, not exponent notation or currency suffixes. Grouping is removed rather than checked for correct placement. A decimal string preserves trailing zeros and avoids an intermediate float; floats may already have lost precision or stringify in exponent notation.

`NumberToWords::convert()` accepts `int|float`, not Persian digit strings. Floats are formatted to at most ten decimal places; precision-loss and range checks can throw `FormatException`. It is not arbitrary-precision decimal arithmetic or a lossless serialization format. See [words to number](words-to-number.md) for the reverse parser.

## Ordinals and time

Ordinal conversion methods require positive integers; zero and negatives throw `FormatException`. `toShort()` defaults to Persian digits and `ام`; custom suffixes are supplied by the caller, not English ordinal grammar.

`TimeAgo::format()` accepts a Unix timestamp, `DateTimeInterface`, or a string parsed by PHP's `strtotime()`. Pass `now` explicitly for deterministic tests. String parsing uses PHP's timezone settings; it is not a Jalali date parser. Month/year buckets use approximate fixed durations. An optional `jalaliMonthResolver` callback adds caller-provided calendar text for year-or-longer differences.

Related: [money](currency.md), [digits](digits.md), [error handling](error-handling.md).
