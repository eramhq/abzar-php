<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Text;

use Eram\Abzar\Text\HtmlSegmenter;
use PHPUnit\Framework\TestCase;

final class HtmlSegmenterTest extends TestCase
{
    public function test_empty_string_returns_empty(): void
    {
        $this->assertSame('', HtmlSegmenter::transformText('', fn (string $s): string => strtoupper($s)));
    }

    public function test_plain_text_is_transformed(): void
    {
        $this->assertSame(
            'HELLO',
            HtmlSegmenter::transformText('hello', fn (string $s): string => strtoupper($s)),
        );
    }

    public function test_tags_are_preserved(): void
    {
        $this->assertSame(
            '<p>HELLO</p>',
            HtmlSegmenter::transformText('<p>hello</p>', fn (string $s): string => strtoupper($s)),
        );
    }

    public function test_script_content_is_preserved(): void
    {
        $html = '<script>alert("x")</script> visible';
        $out = HtmlSegmenter::transformText($html, fn (string $s): string => strtoupper($s));
        $this->assertSame('<script>alert("x")</script> VISIBLE', $out);
    }

    public function test_style_content_is_preserved(): void
    {
        $html = '<style>.a{color:red}</style> outside';
        $out = HtmlSegmenter::transformText($html, fn (string $s): string => strtoupper($s));
        $this->assertSame('<style>.a{color:red}</style> OUTSIDE', $out);
    }

    public function test_html_comments_are_preserved(): void
    {
        $html = '<!-- secret --> visible';
        $out = HtmlSegmenter::transformText($html, fn (string $s): string => strtoupper($s));
        $this->assertSame('<!-- secret --> VISIBLE', $out);
    }

    public function test_attribute_values_are_not_transformed(): void
    {
        $html = '<a href="page-5">Item 5</a>';
        $out = HtmlSegmenter::transformText($html, fn (string $s): string => str_replace('5', 'X', $s));
        $this->assertSame('<a href="page-5">Item X</a>', $out);
    }
}
