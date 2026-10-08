---
title: "Persian text"
description: "Normalize characters, prepare search text, create slugs and handle half-spaces."
---

# Persian text

Use `CharNormalizer` to make Arabic keyboard variants consistent with Persian text. Choose normalization rules deliberately for stored text and search keys.

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Text\{CharNormalizer, HalfSpaceFixer, Slug};

$n = new CharNormalizer();
echo $n->normalize('كتابي ٠١٢'), "\n";
echo $n->normalizeForSearch('۱۲۳ كتاب'), "\n";
echo HalfSpaceFixer::fix('می روم'), "\n";
echo Slug::generate('محصول ۱۲۳'), "\n";
echo $n->normalizeContent('<p title="كتابي">كتابي</p>'), "\n";
```

```text
کتابی ۰۱۲
123 کتاب
می‌روم
محصول-123
<p title="كتابي">کتابی</p>
```

## Options

Default normalization replaces Arabic `ي` / `ك` and Arabic digits; ASCII digits stay ASCII. Constructor flags default to `false`: `tehMarbuta`, `foldHamza`, `stripTashkeel`, `stripKashida`, `stripBidiMarks`, `normalizeToNfc`. Hamza folding preserves `آ`. NFC needs `intl`; the other options do not.

`normalizeForSearch()` applies the configured normalization and converts digits to ASCII. Apply the same configuration to indexed text and queries. It does not tokenize, stem, rank, perform fuzzy matching, build indexes, or make a database's existing rows searchable by itself. It does not remove half-spaces or collapse whitespace.

## HTML and half-spaces

`normalizeContent()` transforms text segments and leaves tags, attributes, entities, comments, and the contents of `script`, `style`, `pre`, `code`, and `textarea` alone. This is a regex-based convenience, not an HTML sanitizer or full DOM parser. Escape or sanitize untrusted HTML separately.

`HalfSpaceFixer` uses affix rules, not a grammar model. It can join a non-verb after `می` incorrectly and does not fix every compound or clean all whitespace. Review bulk changes before saving them.

## Sorting and script checks

```php
use Eram\Abzar\Text\{PersianCollator, Script};

(new PersianCollator())->sort(['ج', 'ب', 'ا']); // ['ا', 'ب', 'ج']; requires intl
Script::isPersian('سلام دنیا'); // true
Script::hasArabic('ك'); // true
```

Script checks use character sets, not language identification. `Slug::generate()` can produce an empty string or duplicate slug; your application handles uniqueness and URL routing. Collation depends on the installed ICU data.

Related: [digits](digits.md), [keyboard fixes](keyboard-fixer.md), [WordPress](recipes/wordpress.md), [errors](error-handling.md).
