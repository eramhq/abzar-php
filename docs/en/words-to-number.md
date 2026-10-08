---
title: "Words to number"
description: "Parse Persian number words into integers or floats."
---

# Words to Number

`Eram\Abzar\Format\WordsToNumber` parses Persian number words back to `int` or `float`. It complements `Eram\Abzar\Format\NumberToWords::convert()`, but floating-point round trips are not guaranteed to preserve every value.

## Minimal example

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Format\WordsToNumber;

var_dump(WordsToNumber::parse('یک هزار و دویست و سی و چهار'));
var_dump(WordsToNumber::parse('سه ممیز پنج'));
var_dump(WordsToNumber::parse('دو سه'));
```

```text
int(1234)
float(3.5)
NULL
```

## More examples

```php
use Eram\Abzar\Format\WordsToNumber;

WordsToNumber::parse('یک هزار و دویست و سی و چهار'); // 1234
WordsToNumber::parse('منفی پنج');                    // -5
WordsToNumber::parse('سه ممیز پنج');                 // 3.5 (float)
WordsToNumber::parse('foo bar');                     // null — unparseable
WordsToNumber::parse('دو سه');                       // null — not a number
WordsToNumber::parse('سه صد');                       // 300 — split hundreds
WordsToNumber::parse('هزار میلیارد');                // 1000000000000
```

## Rules

- Leading `منفی` flips the sign.
- `ممیز` switches to fractional mode — the post-separator integer is divided by `10^digits` and the result becomes a `float`.
- Leading `یک` before `هزار` / `میلیون` / etc. is optional.
- Mixed word + digit input (e.g. `یک هزار و 200`) returns `null`. Normalize to pure words or pure digits first.
- Whitespace and ZWNJ separate tokens; the `و` conjunction is treated as a separator and is optional (`بیست دو` = 22).
- Within each group below a thousand, the words must step down in size: hundreds, then tens, then ones, or a single teen. Sequences such as `دو سه`, `بیست سی` or `یازده دو` return `null` instead of being summed. A ones word followed by `صد` (`سه صد`, `یک صد`) is read as split hundreds.
- `هزار` multiplies the group in front of it, once per group. Larger scales (`میلیون` and up) must appear in decreasing order, so `هزار میلیارد` is accepted but `یک میلیون دو میلیون` returns `null`.
- Besides the forms `NumberToWords` writes, these alternate spellings are accepted: `شیش` (6), `صد` (100), `چارصد` (400), `بیلیون` (10⁹, the same scale as `میلیارد`) and `کوآدریلیون` (10¹⁵). Together with split hundreds, this covers everything persian-tools' `numberToWords()` writes. See [persian-tools parity](persian-tools-parity.md).

## Precision ceiling

Integer results fit in `int` up to `PHP_INT_MAX` (≈ 9.2 × 10¹⁸ on 64-bit PHP; lower on 32-bit PHP). Larger values (e.g. `ده کوینتیلیون`) return `null`. If you need big-integer semantics, use a dedicated math library.

## Common mistakes

Do not coerce `null` to zero: it means parsing failed. Currency suffixes, ordinal words, fuzzy prose and mixed digits/words are not supported. Fractional results are PHP floats, not exact money values. Use integer rials with [Amount](currency.md).

Related: [formatting](formatting.md), [digits](digits.md), [Persian text](persian-text.md).
