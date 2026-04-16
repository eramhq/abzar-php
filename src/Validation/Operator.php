<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

enum Operator: string
{
    use PersianLookup;

    case MCI            = 'mci';
    case IRANCELL       = 'irancell';
    case RIGHTEL        = 'rightel';
    case TALIYA         = 'taliya';
    case SHATEL_MOBILE  = 'shatel-mobile';
    case APTEL          = 'aptel';

    public function persianName(): string
    {
        return match ($this) {
            self::MCI           => 'همراه اول',
            self::IRANCELL      => 'ایرانسل',
            self::RIGHTEL       => 'رایتل',
            self::TALIYA        => 'تالیا',
            self::SHATEL_MOBILE => 'شاتل موبایل',
            self::APTEL         => 'آپتل',
        };
    }
}
