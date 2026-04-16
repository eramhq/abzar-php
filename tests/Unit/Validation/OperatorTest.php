<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\Validation\Operator;
use PHPUnit\Framework\TestCase;

final class OperatorTest extends TestCase
{
    public function test_persian_name_roundtrip(): void
    {
        foreach (Operator::cases() as $case) {
            self::assertSame($case, Operator::fromPersian($case->persianName()));
        }
    }

    public function test_unknown_returns_null(): void
    {
        self::assertNull(Operator::fromPersian('foo'));
    }

    public function test_all_six_cases_present(): void
    {
        self::assertCount(6, Operator::cases());
    }
}
