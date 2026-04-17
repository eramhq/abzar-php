# Bill ID

`Eram\Abzar\Validation\BillId` validates the Iranian bank-utility bill-ID (`شناسه قبض`), optionally paired with its payment-ID (`شناسه پرداخت`).

```php
use Eram\Abzar\Validation\BillId;
use Eram\Abzar\Validation\BillType;

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
```

## Algorithm

- `bill_id` is 6–18 digits. The last digit is a mod-11 checksum over the first N−1 digits; the second-to-last digit encodes the bill type.
- `payment_id` is 6–18 digits. Its last two digits are cross-checksums computed over `bill_id + payment_prefix` and `bill_id + payment_prefix + first_checksum`.

The weighting vector is `[2, 3, 4, 5, 6, 7]` repeated from the rightmost digit.

## Error codes

| Code | When |
|---|---|
| `BILL_ID.EMPTY` | Either input is empty |
| `BILL_ID.WRONG_LENGTH` | Either input is outside 6–18 digits |
| `BILL_ID.INVALID_CHECKSUM` | `bill_id` last digit does not match its mod-11 checksum |
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
