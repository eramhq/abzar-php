# Bill ID

`Eram\Abzar\Validation\BillId` validates the Iranian bank-utility bill-ID (`شناسه قبض`) + payment-ID (`شناسه پرداخت`) pair.

```php
use Eram\Abzar\Validation\BillId;

$result = BillId::validate($billId, $paymentId);
if ($result->isValid()) {
    $type = $result->details()['type']; // water | electric | gas | phone | mobile | tax | services | passport | other
}
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
