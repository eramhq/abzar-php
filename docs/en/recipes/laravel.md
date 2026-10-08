---
title: "Laravel integration"
description: "Add application-owned validation rules and FormRequest adapters."
---

# Laravel — FormRequest / Validation Rules

Abzar does not ship Laravel bridges. Wrap the validators in a thin `Rule` object in your own application code. Three patterns, pick whichever fits your team.

## 1. ValidationRule object (Laravel 10+)

```php
<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Eram\Abzar\Validation\NationalId;
use Illuminate\Contracts\Validation\ValidationRule;

final class IranianNationalId implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('کد ملی باید رشته باشد.');
            return;
        }

        $result = NationalId::validate($value);
        if (!$result->isValid()) {
            foreach ($result->errors() as $error) {
                $fail($error);
            }
        }
    }
}
```

Use in a FormRequest:

```php
public function rules(): array
{
    return [
        'national_id' => ['required', 'string', new \App\Rules\IranianNationalId()],
    ];
}
```

## 2. Closure rule (one-off)

```php
public function rules(): array
{
    return [
        'iban' => ['bail', 'required', 'string', function (string $attr, mixed $value, \Closure $fail): void {
            $result = \Eram\Abzar\Validation\Iban::validate((string) $value);
            if (!$result->isValid()) {
                $fail(implode('؛ ', $result->errors()));
            }
        }],
    ];
}
```

## 3. Service-provider–registered extension

```php
// In AppServiceProvider::boot()
\Illuminate\Support\Facades\Validator::extend('iranian_mobile', function ($attribute, $value, $parameters, $validator) {
    if (!is_string($value)) {
        return false;
    }
    $phone = \Eram\Abzar\Validation\PhoneNumber::tryFrom($value);
    return $phone !== null && $phone->isMobile();
}, 'شماره موبایل معتبر نیست.');
```

For any-phone acceptance (mobile + landline), drop the `->isMobile()` check and rename the rule / message accordingly.

Then: `'phone' => ['required', 'string', 'iranian_mobile']`.

## Surfacing the value object

A FormRequest hook can expose an object to downstream code. The constructor validates again; the object does not reuse an earlier ValidationResult. Keep the hook guarded against invalid input types:

```php
public function after(): array
{
    return [function (\Illuminate\Validation\Validator $validator): void {
        if ($validator->errors()->has('card')) {
            return;
        }
        $input = $this->input('card');
        $card = is_string($input) ? \Eram\Abzar\Validation\CardNumber::tryFrom($input) : null;
        if ($card !== null) {
            $this->merge(['_card' => $card]); // access via $card->bank(), $card->bin()
        }
    }];
}
```

## Result and limitations

Save the rule class in `app/Rules/IranianNationalId.php`. In a bootstrapped Laravel application:

```php
$validator = \Illuminate\Support\Facades\Validator::make(
    ['national_id' => '1234567890'],
    ['national_id' => ['required', 'string', new \App\Rules\IranianNationalId()]],
);
echo $validator->errors()->first('national_id');
```

```text
کد ملی نامعتبر است
```

A valid `0013542419` passes this rule. Empty required fields are handled by Laravel's `required` rule. Validation does not normalize the value stored by Laravel; call `NationalId::from($value)->value()` after successful validation when saving. The rule accepts lookup warnings and does not verify identity. These examples need Laravel and application bootstrapping; Abzar does not install either.

See Laravel's [custom validation rules](https://laravel.com/docs/11.x/validation#custom-validation-rules) for the framework contract.

Related: [integration overview](../framework-integration.md), [validation](../validation.md), [errors](../error-handling.md).
