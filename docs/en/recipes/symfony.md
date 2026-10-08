---
title: "Symfony Validator integration"
description: "Create a constraint and validator for Iranian national IDs."
---

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

## Exposing the value object

After DTO validation, construct the value object in your handler and pass it downstream. This constructor performs validation again:

```php
$card = \Eram\Abzar\Validation\CardNumber::tryFrom($dto->card);
// $card?->bank(), $card?->bin(), $card?->bankEnum() etc.
```

## Result and limitations

Save the constraint and its validator as matching class files in `src/Validator/`. In a Symfony application, enable attribute mapping and the usual service autoconfiguration (or register the validator with `validator.constraint_validator`). The constraint and `NotBlank` apply to the DTO property above.

For `new CustomerDto('1234567890')`, the validation service reports this violation on `nationalId`:

```text
کد ملی معتبر نیست: کد ملی نامعتبر است
```

For `0013542419`, this constraint reports no violations. It deliberately ignores `null`/empty input so `NotBlank` can handle required fields. A valid result does not prove identity, and lookup warnings are accepted. Construction does not change the original DTO string; normalize when saving if needed. The recipe requires Symfony Validator and a configured application.

See Symfony's [custom constraint documentation](https://symfony.com/doc/current/validation/custom_constraint.html).

Related: [integration overview](../framework-integration.md), [national ID](../national-id.md), [errors](../error-handling.md).
