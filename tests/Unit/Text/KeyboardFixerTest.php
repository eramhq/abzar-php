<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Text;

use Eram\Abzar\Text\KeyboardFixer;
use PHPUnit\Framework\TestCase;

final class KeyboardFixerTest extends TestCase
{
    public function test_en_to_fa_salam(): void
    {
        self::assertSame('سلام', KeyboardFixer::enToFa('sghl'));
    }

    public function test_en_to_fa_lowercases_input(): void
    {
        self::assertSame('سلام', KeyboardFixer::enToFa('SGHL'));
    }

    public function test_fa_to_en_roundtrip(): void
    {
        self::assertSame('sghl', KeyboardFixer::faToEn('سلام'));
    }

    public function test_roundtrip_identity_lowercase(): void
    {
        self::assertSame('sghl', KeyboardFixer::faToEn(KeyboardFixer::enToFa('sghl')));
    }

    public function test_preserves_digits(): void
    {
        self::assertSame('ض12', KeyboardFixer::enToFa('q12'));
    }

    public function test_preserves_whitespace_and_unmapped(): void
    {
        self::assertSame('ض ص', KeyboardFixer::enToFa('q w'));
    }

    public function test_preserves_persian_characters_unchanged(): void
    {
        self::assertSame('سلام', KeyboardFixer::enToFa('سلام'));
    }

    public function test_preserves_persian_digits(): void
    {
        self::assertSame('۱۲۳', KeyboardFixer::enToFa('۱۲۳'));
    }

    public function test_preserves_ascii_digits(): void
    {
        self::assertSame('0123456789', KeyboardFixer::enToFa('0123456789'));
    }

    // ── detect() ──────────────────────────────────────────────────────

    public function test_detect_fingerprints_salam_typed_on_en_layout(): void
    {
        self::assertTrue(KeyboardFixer::detect('sghl'));
    }

    public function test_detect_does_not_fire_on_normal_english(): void
    {
        self::assertFalse(KeyboardFixer::detect('hello world'));
    }

    public function test_detect_false_on_persian_input(): void
    {
        self::assertFalse(KeyboardFixer::detect('سلام'));
    }

    public function test_detect_false_on_empty(): void
    {
        self::assertFalse(KeyboardFixer::detect(''));
    }

    public function test_detect_false_on_single_letter(): void
    {
        self::assertFalse(KeyboardFixer::detect('a'));
    }
}
