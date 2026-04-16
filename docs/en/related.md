# Related Packages

Abzar is scoped to framework-agnostic Persian utilities. These companion packages cover adjacent ground.

## Maintained by eramhq

### [`eramhq/daynum`](https://github.com/eramhq/daynum)

Jalali / Shamsi calendar utilities: parsing, formatting, month names, leap-year arithmetic, conversion to and from Gregorian. Abzar does **not** ship calendar logic and will not grow jalali support in-tree — use daynum for anything date-related.

```bash
composer require eramhq/daynum
```

### [`eramhq/persian-kit`](https://github.com/eramhq/persian-kit)

WordPress plugin that wires abzar into WP hooks (`the_content`, `sanitize_title`, `pre_get_posts`), ships admin screens for one-shot database normalization, and exposes shortcodes and Gutenberg blocks. If you're building on WordPress, start here rather than wiring abzar yourself.

## Community

### [persian-tools/persian-tools](https://github.com/persian-tools/persian-tools) (JavaScript)

The JavaScript library that inspired several of abzar's algorithms and data tables. Useful as a cross-reference for validators and fixture data; parity tests are planned.

### [nikapps/iran-validator](https://github.com/nikapps/iran-validator) (PHP)

An older PHP library covering a subset of Iranian validators. Differences versus abzar:

| | abzar | nikapps/iran-validator |
|---|---|---|
| Zero runtime deps | Yes | Yes |
| Typed `ValidationResult` return | Yes (`isValid / errors / details`) | Boolean only |
| Structured error codes (`0.3+`) | Yes (planned) | No |
| Formatter / text / digit utilities | Yes | Validation only |
| PHP version baseline | 8.1+ | 7.4+ |
| Recent releases | Active | Dormant |
| WordPress integration | Via `eramhq/persian-kit` | No |

Abzar aims to cover a broader surface with a modern typed API; `nikapps/iran-validator` is a reasonable pick if you only need validators and cannot move off PHP 7.
