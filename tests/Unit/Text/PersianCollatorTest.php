<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Text;

use Eram\Abzar\Text\PersianCollator;
use PHPUnit\Framework\TestCase;

final class PersianCollatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!class_exists(\Collator::class)) {
            self::markTestSkipped('ext-intl not available; PersianCollator tests require it.');
        }
    }

    public function test_compare_orders_persian_alphabetically(): void
    {
        $c = new PersianCollator();
        // ا < ب alphabetically; native sort orders by codepoint (ا=U+0627, ب=U+0628).
        // This test just asserts Collator returns a sign consistent with ordering.
        $this->assertLessThan(0, $c->compare('ا', 'ب'));
        $this->assertGreaterThan(0, $c->compare('ب', 'ا'));
        $this->assertSame(0, $c->compare('الف', 'الف'));
    }

    public function test_sort_reorders_words(): void
    {
        $c = new PersianCollator();
        $sorted = $c->sort(['ج', 'ب', 'ا']);
        $this->assertSame(['ا', 'ب', 'ج'], array_values($sorted));
    }

    public function test_sort_by_extracts_key(): void
    {
        $c = new PersianCollator();
        $rows = [
            ['name' => 'ج', 'n' => 3],
            ['name' => 'ا', 'n' => 1],
            ['name' => 'ب', 'n' => 2],
        ];
        $out = $c->sortBy($rows, static fn (array $row): string => $row['name']);
        $this->assertSame([1, 2, 3], array_column($out, 'n'));
    }
}
