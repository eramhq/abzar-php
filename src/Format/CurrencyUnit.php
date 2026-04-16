<?php

declare(strict_types=1);

namespace Eram\Abzar\Format;

enum CurrencyUnit: string
{
    case TOMAN = 'toman';
    case RIAL  = 'rial';

    public function persianName(): string
    {
        return match ($this) {
            self::TOMAN => 'تومان',
            self::RIAL  => 'ریال',
        };
    }
}
