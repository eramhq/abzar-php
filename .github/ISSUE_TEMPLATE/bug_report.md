---
name: Bug report
about: Something in Abzar produced a wrong result
title: "[Bug] "
labels: bug
---

**Summary**

<!-- One line: what did you try, what happened, what did you expect? -->

**Reproduction**

```php
// Minimal PHP snippet. Paste the exact input string — including Persian characters, whitespace, ZWNJ, etc.
use Eram\Abzar\Validation\NationalId;

$result = NationalId::validate('...');
var_export($result->isValid());
var_export($result->errorCodes());
var_export($result->detail());
```

**Expected**

<!-- What should the output have been? -->

**Actual**

<!-- What was the output? -->

**Environment**

- Abzar version:
- PHP version (`php -v`):
- OS:
- Installed via composer? (yes / no)

**Additional context**

<!-- Data source for the expected value, upstream reference, or any other relevant notes. -->
