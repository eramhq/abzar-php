# Symfony — Validator Component

Abzar doesn't ship a Symfony bridge. Build a constraint + validator pair in your own code.

## Constraint

```php
<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
final class IranianNationalId extends Constraint
{
    public string $message = 'کد ملی معتبر نیست: {{ error }}';
}
```

## Validator

```php
<?php

declare(strict_types=1);

namespace App\Validator;

use Eram\Abzar\Validation\NationalId;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class IranianNationalIdValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof IranianNationalId) {
            throw new UnexpectedTypeException($constraint, IranianNationalId::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $result = NationalId::validate($value);
        if ($result->isValid()) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ error }}', $result->errors()[0] ?? 'نامعتبر')
            ->addViolation();
    }
}
```

## Usage in a DTO

```php
use App\Validator\IranianNationalId;
use Symfony\Component\Validator\Constraints as Assert;

final class CustomerDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[IranianNationalId]
        public string $nationalId,
    ) {
    }
}
```

## Exposing `details()`

If a downstream service needs the bank / operator / city lookup details, don't re-validate. Call the abzar validator once in your command/handler and pass the `details()` array alongside the DTO:

```php
$result = \Eram\Abzar\Validation\CardNumber::validate($dto->card);
$cardDetails = $result->isValid() ? $result->details() : null;
```
