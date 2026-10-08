---
title: "استفاده در Symfony Validator"
description: "ساخت constraint و validator برای کد ملی در Symfony."
---

# استفاده در Symfony Validator

ابزار bridge آماده Symfony ندارد. یک constraint و validator در کد برنامه بسازید و آن‌ها را در فایل‌های جداگانه با نام کلاس در `src/Validator/` قرار دهید.

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

## استفاده در DTO

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

attribute mapping و autoconfiguration سرویس‌ها باید فعال باشند؛ در تنظیم دستی، validator را با تگ `validator.constraint_validator` ثبت کنید. `NotBlank` خالی بودن مقدار را جداگانه بررسی می‌کند.

## ساخت value object

بعد از بررسی DTO می‌توانید object را برای سرویس بعدی بسازید. این کار اعتبارسنجی را دوباره انجام می‌دهد:

```php
$card = \Eram\Abzar\Validation\CardNumber::tryFrom($dto->card);
// $card?->bank(), $card?->bin(), $card?->bankEnum() etc.
```

## خروجی و محدودیت‌ها

برای `new CustomerDto('1234567890')`، سرویس validation روی property به نام `nationalId` این violation را می‌دهد:

```text
کد ملی معتبر نیست: کد ملی نامعتبر است
```

برای `0013542419` این constraint خطایی ندارد. مقدار خالی و `null` را کنار می‌گذارد تا `NotBlank` تصمیم بگیرد. lookup دارای هشدار پذیرفته می‌شود و هویت بررسی نمی‌شود. مقدار DTO خودکار نرمال نمی‌شود؛ موقع ذخیره در صورت نیاز مقدار نرمال را بگیرید. اجرای این نمونه نیازمند Symfony Validator و برنامه تنظیم‌شده است.

جزئیات قرارداد در [مستندات constraint سفارشی Symfony](https://symfony.com/doc/current/validation/custom_constraint.html) آمده است.

مطالب مرتبط: [فریم‌ورک‌ها](../framework-integration.md)، [کد ملی](../national-id.md)، [خطاها](../error-handling.md).
