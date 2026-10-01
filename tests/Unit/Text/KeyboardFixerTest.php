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

    /**
     * Shift layer of the ISIRI 9147 standard layout — since 0.7 it maps it instead of
     * lowercasing, so Shift+H yields آ rather than ا.
     *
     * @return iterable<string, array{string, string}>
     */
    public static function shiftLayer(): iterable
    {
        yield 'alef madda'  => ['Hfhn', 'آباد'];
        yield 'zhe'         => ['Chgi', 'ژاله'];
        yield 'hamza'       => ['Mlhl', 'ءمام'];
        yield 'zwnj'        => ['ldBv,l', "می\u{200C}روم"];
        yield 'guillemets'  => ['Lsghl K', '«سلام »'];
        yield 'tashkeel'    => ['sUgh', 'سَلا'];
    }

    /**
     * @dataProvider shiftLayer
     */
    public function test_en_to_fa_shift_layer(string $typed, string $expected): void
    {
        self::assertSame($expected, KeyboardFixer::enToFa($typed));
    }

    /**
     * @dataProvider shiftLayer
     */
    public function test_fa_to_en_shift_layer_roundtrip(string $typed, string $persian): void
    {
        self::assertSame($typed, KeyboardFixer::faToEn($persian));
    }

    public function test_fa_to_en_leaves_ascii_brackets_alone(): void
    {
        // Shift+O / Shift+P produce ASCII brackets, which are far more often
        // literal in Persian text than evidence of a layout slip.
        self::assertSame('[sghl]', KeyboardFixer::faToEn('[سلام]'));
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
