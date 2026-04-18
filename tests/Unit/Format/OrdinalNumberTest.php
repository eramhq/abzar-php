<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Format;

use Eram\Abzar\Exception\FormatException;
use Eram\Abzar\Format\OrdinalNumber;
use PHPUnit\Framework\TestCase;

class OrdinalNumberTest extends TestCase
{
    // --- toWord ---

    public function test_to_word_first(): void
    {
        $this->assertSame('یکم', OrdinalNumber::toWord(1));
    }

    public function test_to_word_second(): void
    {
        $this->assertSame('دوم', OrdinalNumber::toWord(2));
    }

    public function test_to_word_third(): void
    {
        $this->assertSame('سوم', OrdinalNumber::toWord(3));
    }

    public function test_to_word_tenth(): void
    {
        $this->assertSame('دهم', OrdinalNumber::toWord(10));
    }

    public function test_to_word_thirty(): void
    {
        // "سی" ends with "ی" → "سی اُم"
        $this->assertSame('سی اُم', OrdinalNumber::toWord(30));
    }

    public function test_to_word_compound(): void
    {
        // 43 = "چهل و سه" → "چهل و سوم"
        $this->assertSame('چهل و سوم', OrdinalNumber::toWord(43));
    }

    public function test_to_word_thousand(): void
    {
        $this->assertSame('یک هزارم', OrdinalNumber::toWord(1000));
    }

    public function test_to_word_zero_throws(): void
    {
        $this->expectException(FormatException::class);
        OrdinalNumber::toWord(0);
    }

    public function test_to_word_negative_throws(): void
    {
        $this->expectException(FormatException::class);
        OrdinalNumber::toWord(-1);
    }

    // --- toShort ---

    public function test_to_short_persian_digits(): void
    {
        $this->assertSame('۴۳ام', OrdinalNumber::toShort(43));
    }

    public function test_to_short_english_digits(): void
    {
        $this->assertSame('43ام', OrdinalNumber::toShort(43, 'english'));
    }

    public function test_to_short_first(): void
    {
        $this->assertSame('۱ام', OrdinalNumber::toShort(1));
    }

    public function test_to_short_english_digits_english_suffix(): void
    {
        // Callers asking for English digits get to pick a matching suffix rather
        // than receive hybrid-script output like "43ام".
        $this->assertSame('43rd', OrdinalNumber::toShort(43, 'english', 'rd'));
    }

    public function test_to_short_empty_suffix(): void
    {
        $this->assertSame('۴۳', OrdinalNumber::toShort(43, 'persian', ''));
    }

    public function test_to_short_zero_throws(): void
    {
        $this->expectException(FormatException::class);
        OrdinalNumber::toShort(0);
    }

    // --- addSuffix ---

    public function test_add_suffix_ending_with_se(): void
    {
        $this->assertSame('سوم', OrdinalNumber::addSuffix('سه'));
    }

    public function test_add_suffix_ending_with_ye(): void
    {
        $this->assertSame('سی اُم', OrdinalNumber::addSuffix('سی'));
    }

    public function test_add_suffix_default(): void
    {
        $this->assertSame('دهم', OrdinalNumber::addSuffix('ده'));
    }

    public function test_add_suffix_empty_throws(): void
    {
        $this->expectException(FormatException::class);
        OrdinalNumber::addSuffix('');
    }
}
