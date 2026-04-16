# Security Policy

## Supported Versions

Abzar is currently on the `0.x` line. During this phase, only the latest minor release receives security fixes. Once `1.0` lands, the most recent minor plus the previous minor will each get security fixes for six months after superseded.

| Version | Supported |
| ------- | --------- |
| 0.x (latest minor) | Yes |
| older 0.x releases | No  |

## Reporting a Vulnerability

Please **do not** open a public issue for security problems. Instead, email the maintainers at:

- security@eramhq.dev

Include:
- A description of the issue and the impact you observed.
- A minimal reproduction (input strings, method calls, stack trace).
- The Abzar version, PHP version, and OS.
- Whether the issue is already public or embargoed elsewhere.

You should receive an acknowledgement within two business days. We aim to confirm or dispute the report within seven days and ship a fix (or a coordinated disclosure timeline) within thirty days for confirmed issues.

## Scope

In scope:
- Logic bugs that cause a validator to accept clearly invalid input (false positives) or reject clearly valid input (false negatives) in a way that has security implications for a consumer (e.g. IBAN mod-97 bypass, Luhn bypass, national-ID checksum bypass).
- Algorithmic complexity issues (ReDoS) in the text-processing pipelines (`CharNormalizer::normalizeContent`, `DigitConverter::convertContent`, `Slug::generate`).
- Information leakage in exception messages or serialized output.

Out of scope:
- Persian message strings — they are end-user UI copy and may change between minor releases.
- The contents of lookup tables (city codes, bank BINs, operator prefixes); data corrections go via normal issues/PRs.
- Issues that require modifying the library's own source or composer dependencies before reproducing.
