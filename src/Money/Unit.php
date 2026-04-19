<?php

declare(strict_types=1);

namespace Eram\Abzar\Money;

enum Unit: string
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
