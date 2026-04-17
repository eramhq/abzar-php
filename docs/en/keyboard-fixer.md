# Keyboard Fixer

`Eram\Abzar\Text\KeyboardFixer` swaps between English QWERTY and the standard Iranian Persian keyboard (`fa-IR`) layout. Use it when a user typed with the wrong layout active — e.g. pressed the keys for `سلام` while English was engaged and produced `sghl`.

```php
use Eram\Abzar\Text\KeyboardFixer;

KeyboardFixer::enToFa('sghl');   // سلام
KeyboardFixer::faToEn('سلام');   // sghl
KeyboardFixer::enToFa('SGHL');   // سلام — input is lowercased first

// Optional heuristic: was this input typed with the wrong layout?
KeyboardFixer::detect('sghl');     // true
KeyboardFixer::detect('hello');    // false (normal English vowel ratio)
KeyboardFixer::detect('سلام');     // false (already Persian)
```

## Behaviour

- Only Latin letters (lower- and uppercase) and a small set of punctuation are mapped. Digits, whitespace, Persian, Arabic, kashida, and ZWNJ pass through unchanged.
- `enToFa()` lowercases its input before mapping. Round-trips preserve the lowercase form: `faToEn(enToFa('SGHL')) === 'sghl'`.
- The layout mapping is the standard Iranian Persian keyboard. Dari, Pashto, and other regional variants are out of scope.
- `detect()` is a coarse character-script entropy heuristic, not grammar-aware. It returns `true` when the input is ASCII-letter-only and the vowel ratio is below ~25% — the fingerprint of a Persian word typed with the English layout. Mixed-script or already-Persian input never triggers. Do not call `enToFa()` unconditionally on `detect() === true` without giving users an opt-out.
