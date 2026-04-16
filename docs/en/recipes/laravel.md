# Laravel — FormRequest / Validation Rules

Abzar does not ship Laravel bridges. Wrap the validators in a thin `Rule` object in your own application code. Three patterns, pick whichever fits your team.

## 1. Invokable rule (Laravel 10+)

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
        'national_id' => ['required', 'string', new IranianNationalId()],
    ];
}
```

## 2. Closure rule (one-off)

```php
public function rules(): array
{
    return [
        'iban' => ['required', 'string', function (string $attr, mixed $value, Closure $fail): void {
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
Validator::extend('iranian_mobile', function ($attribute, $value, $parameters, $validator) {
    return \Eram\Abzar\Validation\PhoneNumber::validate((string) $value)->isValid();
}, 'شماره موبایل معتبر نیست.');
```

Then: `'phone' => ['required', 'string', 'iranian_mobile']`.

## Surfacing the value object

If you need the lookup details (bank, operator, city/province) in your controller, don't re-validate — call the value-object constructor once, attach the instance to the request, and use them downstream:

```php
public function after(): array
{
    return [function (\Illuminate\Validation\Validator $validator): void {
        $card = \Eram\Abzar\Validation\CardNumber::tryFrom($this->input('card'));
        if ($card !== null) {
            $this->merge(['_card' => $card]); // access via $card->bank(), $card->bin()
        }
    }];
}
```
