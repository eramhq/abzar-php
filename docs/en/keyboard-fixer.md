# Keyboard Fixer

`Eram\Abzar\Text\KeyboardFixer` swaps between English QWERTY and the standard Iranian Persian keyboard (`fa-IR`) layout. Use it when a user typed with the wrong layout active — e.g. pressed the keys for `سلام` while English was engaged and produced `sghl`.

```php
use Eram\Abzar\Text\KeyboardFixer;

KeyboardFixer::enToFa('sghl');   // سلام
KeyboardFixer::faToEn('سلام');   // sghl
KeyboardFixer::enToFa('SGHL');   // سلام — input is lowercased first
```

## Behaviour

- Only Latin letters (lower- and uppercase) and a small set of punctuation are mapped. Digits, whitespace, Persian, Arabic, kashida, and ZWNJ pass through unchanged.
- `enToFa()` lowercases its input before mapping. Round-trips preserve the lowercase form: `faToEn(enToFa('SGHL')) === 'sghl'`.
- The layout mapping is the standard Iranian Persian keyboard. Dari, Pashto, and other regional variants are out of scope.
