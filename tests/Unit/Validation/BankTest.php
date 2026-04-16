<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\Validation\Bank;
use PHPUnit\Framework\TestCase;

final class BankTest extends TestCase
{
    public function test_persian_name_roundtrip(): void
    {
        foreach (Bank::cases() as $case) {
            self::assertSame($case, Bank::fromPersian($case->persianName()));
        }
    }

    public function test_card_surface_form_alias(): void
    {
        self::assertSame(Bank::MARKAZI, Bank::fromPersian('بانک مرکزی ایران'));
        self::assertSame(Bank::MARKAZI, Bank::fromPersian('بانک مرکزی جمهوری اسلامی ایران'));
        self::assertSame(Bank::KOSAR,   Bank::fromPersian('موسسه کوثر'));
        self::assertSame(Bank::KOSAR,   Bank::fromPersian('موسسه اعتباری کوثر'));
        self::assertSame(Bank::NOOR,    Bank::fromPersian('موسسه نور'));
        self::assertSame(Bank::NOOR,    Bank::fromPersian('موسسه اعتباری نور'));
    }

    public function test_unknown_returns_null(): void
    {
        self::assertNull(Bank::fromPersian('foo'));
        self::assertNull(Bank::fromPersian(''));
    }

    public function test_defunct_flag(): void
    {
        self::assertTrue(Bank::ANSAR->isDefunct());
        self::assertTrue(Bank::HEKMAT_IRANIAN->isDefunct());
        self::assertFalse(Bank::MELLI->isDefunct());
        self::assertFalse(Bank::PARSIAN->isDefunct());
    }

    public function test_backing_values_are_slugs(): void
    {
        foreach (Bank::cases() as $case) {
            self::assertMatchesRegularExpression('/^[a-z][a-z0-9-]*$/', $case->value);
        }
    }
}
