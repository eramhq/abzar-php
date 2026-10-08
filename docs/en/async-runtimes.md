---
title: "Long-running PHP workers"
description: "Use Abzar in persistent workers without storing request data in library state."
---

# Long-running PHP workers

Abzar's PHP code uses bundled data and synchronous computation. Its static caches contain source-derived lookup tables, word maps, or default normalizers. There is no public global configuration setter or cache of request input.

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\PhoneNumber;

foreach (['09121234567', 'invalid', '02188887777'] as $input) {
    echo PhoneNumber::tryFrom($input)?->value() ?? 'invalid';
    echo "\n";
}
```

```text
09121234567
invalid
02188887777
```

## Integration limits

For ordinary sequential jobs in Octane or RoadRunner, the current implementation needs no Abzar-specific reset hook. Create or pass configured normalizers explicitly. `CharNormalizer` is final and cannot be subclassed.

Swoole and ReactPHP callers should treat these calls as synchronous CPU work: there is no promise or asynchronous I/O API. Large text processing still occupies the worker. This source review is not a blanket thread-safety guarantee or a tested runtime support matrix. Manage your application's retained references, native extension objects and worker lifecycle according to its runtime.

Related: [framework integration](framework-integration.md), [Persian text](persian-text.md), [stability](api-stability.md).
