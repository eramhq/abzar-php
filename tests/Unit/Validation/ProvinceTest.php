<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\Validation\Province;
use PHPUnit\Framework\TestCase;

final class ProvinceTest extends TestCase
{
    public function test_persian_name_roundtrip(): void
    {
        foreach (Province::cases() as $case) {
            self::assertSame($case, Province::fromPersian($case->persianName()));
        }
    }

    public function test_arabic_yeh_variant_normalizes(): void
    {
        // آذربايجان شرقي uses Arabic Yeh (U+064A) instead of Persian Yeh (U+06CC).
        self::assertSame(Province::AZARBAIJAN_SHARGHI, Province::fromPersian('آذربايجان شرقي'));
    }

    public function test_unknown_returns_null(): void
    {
        self::assertNull(Province::fromPersian('foo'));
    }

    public function test_31_cases_present(): void
    {
        self::assertCount(31, Province::cases());
    }
}
