# Words to Number

`Eram\Abzar\Format\WordsToNumber` parses Persian number words back to `int` or `float`. Inverse of `Eram\Abzar\Format\NumberToWords::convert()`.

```php
use Eram\Abzar\Format\WordsToNumber;

WordsToNumber::parse('یک هزار و دویست و سی و چهار'); // 1234
WordsToNumber::parse('منفی پنج');                    // -5
WordsToNumber::parse('سه ممیز پنج');                 // 3.5 (float)
WordsToNumber::parse('foo bar');                     // null — unparseable
```

## Rules

- Leading `منفی` flips the sign.
- `ممیز` switches to fractional mode — the post-separator integer is divided by `10^digits` and the result becomes a `float`.
- Leading `یک` before `هزار` / `میلیون` / etc. is optional.
- Mixed word + digit input (e.g. `یک هزار و 200`) returns `null`. Normalize to pure words or pure digits first.
- Whitespace and ZWNJ separate tokens; the `و` conjunction is treated as a separator.

## Precision ceiling

Integer results fit in `int` up to `PHP_INT_MAX` (≈ 9.2 × 10¹⁸). Larger values silently overflow to `float` with the usual IEEE-754 precision loss. If you need big-integer semantics, fall back to a dedicated math library.
