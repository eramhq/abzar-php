---
title: "Bill and payment IDs"
description: "Validate bill checksums and paired payment identifiers."
---

# Bill ID

`Eram\Abzar\Validation\BillId` validates the Iranian bank-utility bill-ID (`شناسه قبض`), optionally paired with its payment-ID (`شناسه پرداخت`).

## Minimal example

Save beside `vendor/` and run with PHP:

```php
<?php
require 'vendor/autoload.php';

use Eram\Abzar\Validation\BillId;

$bill = BillId::from('7748317800142', '1770160');
echo $bill->type()->value, "\n";
var_dump(BillId::validatePair('7748317800142', '1770199')->isValid());
```

```text
phone
bool(false)
```

## More examples

```php
use Eram\Abzar\Validation\BillId;
use Eram\Abzar\Validation\BillType;

$billId = '7748317800142';
$paymentId = '1770160';

// Single-field: many systems store only the bill ID.
$r = BillId::validate($billId);
$r->isValid();
$r->detail()?->type;                 // BillType enum; paymentId is null

// Pair validation + VO construction.
$bill = BillId::tryFrom($billId, $paymentId);
if ($bill !== null) {
    $type = $bill->type();           // BillType enum (WATER, ELECTRIC, GAS, PHONE, MOBILE, TAX, SERVICES, PASSPORT, OTHER)
    $typeString = $type->value;      // 'water' | 'electric' | ...
}

// Same cross-checksum as ::from / ::tryFrom, without constructing a VO.
BillId::validatePair($billId, $paymentId)->isValid();

// Fixtures: a 13-digit bill ID (optionally of a given type) and a payment ID
// that cross-validates against it.
$fakeBill    = BillId::fake(BillType::ELECTRIC);
$fakePayment = BillId::fakePaymentId($fakeBill);
```

## Algorithm

- `bill_id` is 6–13 digits. The last digit is a mod-11 checksum over the first N−1 digits; the second-to-last digit encodes the bill type.
- `payment_id` is 6–18 digits. Its last two digits are cross-checksums computed over `bill_id + payment_prefix` and `bill_id + payment_prefix + first_checksum`.

The weighting vector is `[2, 3, 4, 5, 6, 7]` repeated from the rightmost digit.

## Error codes

| Code | When |
|---|---|
| `BILL_ID.EMPTY` | `bill_id` is empty |
| `BILL_ID.WRONG_LENGTH` | `bill_id` is outside 6–13 digits |
| `BILL_ID.INVALID_CHECKSUM` | `bill_id` last digit does not match its mod-11 checksum |
| `BILL_ID.PAYMENT_EMPTY` | `payment_id` is empty (pair validation only) |
| `BILL_ID.PAYMENT_WRONG_LENGTH` | `payment_id` is outside 6–18 digits |
| `BILL_ID.PAYMENT_MISMATCH` | `payment_id` cross-checksum does not match `bill_id` |

## Type decoding

Last-digit-before-checksum of the bill ID:

| Digit | Type |
|---|---|
| 1 | water |
| 2 | electric |
| 3 | gas |
| 4 | phone |
| 5 | mobile |
| 6 | tax |
| 8 | services |
| 9 | passport |
| other | `other` |

## Limitations and common mistakes

`validate($billId)` checks a bill alone; `from()` and `tryFrom()` require both IDs. `validatePair()` checks cross-checksums, not outstanding debt, payment status, or a live bill issuer. Keep both fields as strings.

Related: [validation](validation.md), [error handling](error-handling.md), [error codes](error-codes.md).
