<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

enum PhoneNumberType: string
{
    case MOBILE   = 'mobile';
    case LANDLINE = 'landline';
}
