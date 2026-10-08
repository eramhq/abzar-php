---
title: "Keyboard layout fixes"
description: "Convert text typed with the wrong Persian or English keyboard layout."
---

# Keyboard Fixer

`Eram\Abzar\Text\KeyboardFixer` swaps between English QWERTY and the standard Iranian Persian keyboard (`fa-IR`) layout. Use it when a user typed with the wrong layout active — e.g. pressed the keys for `سلام` while English was engaged and produced `sghl`.

## Minimal example

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Text\KeyboardFixer;

echo KeyboardFixer::enToFa('sghl'), "\n";
echo KeyboardFixer::faToEn('سلام'), "\n";
echo KeyboardFixer::enToFa('ldBv,l'), "\n";
var_dump(KeyboardFixer::detect('hello'));
```

```text
سلام
sghl
می‌روم
bool(false)
```

## More examples

```php
use Eram\Abzar\Text\KeyboardFixer;

KeyboardFixer::enToFa('sghl');   // سلام
KeyboardFixer::faToEn('سلام');   // sghl
KeyboardFixer::enToFa('Hfhn');   // آباد — Shift+H is آ (ISIRI 9147 Shift layer)
KeyboardFixer::enToFa('ldBv,l'); // می‌روم — Shift+B is ZWNJ

// Optional heuristic: was this input typed with the wrong layout?
KeyboardFixer::detect('sghl');     // true
KeyboardFixer::detect('hello');    // false (normal English vowel ratio)
KeyboardFixer::detect('سلام');     // false (already Persian)
```

## Behaviour

- `enToFa()` maps Latin letters and a small set of punctuation. Digits, whitespace, Persian/Arabic letters, kashida and existing ZWNJ pass through unchanged in that direction. `faToEn()` reverses mapped Persian characters, including the mapped ZWNJ.
- Upper-case letters follow the Shift layer of the ISIRI 9147 standard layout: `H` → `آ`, `C` → `ژ`, `M` → `ء`, `B` → ZWNJ, `K` / `L` → `»` / `«`, `A` → `ؤ`, `S` → `ئ`, the top row → tashkeel, and so on. `faToEn()` reverses it, so `faToEn(enToFa('Hfhn')) === 'Hfhn'`. ASCII `[` / `]` (Shift+P / Shift+O) are left alone by `faToEn()`.
- Text typed with Caps Lock on is not the Shift layer. Lower-case it first if that's your case: `enToFa(strtolower($typed))`. Before 0.7, `enToFa()` lower-cased every input.
- The layout mapping is the standard Iranian Persian keyboard. Dari, Pashto, and other regional variants are out of scope.
- `detect()` is a coarse character-script entropy heuristic, not grammar-aware. It rejects input containing the Persian/Arabic characters detected by `Script`, then counts only ASCII letters after lowercasing. At least two letters and a vowel ratio strictly below 25% yield `true`; digits, punctuation and other scripts are ignored in that count. It is not a general mixed-script detector. Treat it as a signal for *suggesting* a layout swap to the user, not for auto-applying one: consonant-heavy English tokens (brand names like `chatgpt`, acronyms, or short technical jargon) will false-positive. Do not call `enToFa()` unconditionally on `detect() === true` without giving users an opt-out.

## Related guides

See [Persian text](persian-text.md) for character normalization, [digits](digits.md) for digit conversion, and [formatting](formatting.md) for numeric display. Layout conversion is not transliteration or spelling correction.
