<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

/**
 * Iranian utility bill type encoded in the second-to-last digit of `شناسه قبض`.
 *
 * Unknown type digits (0, 7) decode to {@see self::OTHER} — a documented
 * leniency over the upstream persian-tools library, which rejects them.
 */
enum BillType: string
{
    case WATER    = 'water';
    case ELECTRIC = 'electric';
    case GAS      = 'gas';
    case PHONE    = 'phone';
    case MOBILE   = 'mobile';
    case TAX      = 'tax';
    case SERVICES = 'services';
    case PASSPORT = 'passport';
    case OTHER    = 'other';

    public static function fromTypeDigit(int $digit): self
    {
        return match ($digit) {
            1 => self::WATER,
            2 => self::ELECTRIC,
            3 => self::GAS,
            4 => self::PHONE,
            5 => self::MOBILE,
            6 => self::TAX,
            8 => self::SERVICES,
            9 => self::PASSPORT,
            default => self::OTHER,
        };
    }
}
