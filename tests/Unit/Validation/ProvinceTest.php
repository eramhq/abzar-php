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

    public function test_32_cases_present(): void
    {
        self::assertCount(32, Province::cases());
    }

    public function test_alborz_is_a_province(): void
    {
        self::assertSame(Province::ALBORZ, Province::fromPersian('البرز'));
    }

    public function test_kohgiluyeh_canonical_spelling_and_legacy_alias(): void
    {
        self::assertSame('کهگیلویه و بویراحمد', Province::KOHGILUYEH->persianName());
        // The pre-0.7 tables spelled it with ک instead of گ; keep resolving it.
        self::assertSame(Province::KOHGILUYEH, Province::fromPersian('کهکیلویه و بویراحمد'));
    }
}
