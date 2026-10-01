# Keyboard Fixer

`Eram\Abzar\Text\KeyboardFixer` swaps between English QWERTY and the standard Iranian Persian keyboard (`fa-IR`) layout. Use it when a user typed with the wrong layout active — e.g. pressed the keys for `سلام` while English was engaged and produced `sghl`.

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

- Only Latin letters and a small set of punctuation are mapped. Digits, whitespace, Persian, Arabic, kashida, and ZWNJ pass through unchanged.
- Upper-case letters follow the Shift layer of the ISIRI 9147 standard layout: `H` → `آ`, `C` → `ژ`, `M` → `ء`, `B` → ZWNJ, `K` / `L` → `»` / `«`, `A` → `ؤ`, `S` → `ئ`, the top row → tashkeel, and so on. `faToEn()` reverses it, so `faToEn(enToFa('Hfhn')) === 'Hfhn'`. ASCII `[` / `]` (Shift+P / Shift+O) are left alone by `faToEn()`.
- Text typed with Caps Lock on is not the Shift layer. Lower-case it first if that's your case: `enToFa(strtolower($typed))`. Before 0.7, `enToFa()` lower-cased every input.
- The layout mapping is the standard Iranian Persian keyboard. Dari, Pashto, and other regional variants are out of scope.
- `detect()` is a coarse character-script entropy heuristic, not grammar-aware. It returns `true` when the input is ASCII-letter-only and the vowel ratio is below ~25% — the fingerprint of a Persian word typed with the English layout. Mixed-script or already-Persian input never triggers. Treat it as a signal for *suggesting* a layout swap to the user, not for auto-applying one: consonant-heavy English tokens (brand names like `chatgpt`, acronyms, or short technical jargon) will false-positive. Do not call `enToFa()` unconditionally on `detect() === true` without giving users an opt-out.
