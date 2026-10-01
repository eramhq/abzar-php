<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Text;

use Eram\Abzar\Exception\FormatException;
use Eram\Abzar\Text\HtmlSegmenter;
use Eram\Abzar\Validation\ErrorCode;
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

    /**
     * B6 — character references must survive a text transform untouched.
     *
     * @return iterable<string, array{string, string}>
     */
    public static function entities(): iterable
    {
        yield 'decimal ZWNJ'  => ['a&#8204;b 12', 'a&#8204;b XX'];
        yield 'hex ZWNJ'      => ['a&#x200C;b 12', 'a&#x200C;b XX'];
        yield 'named'         => ['&nbsp;12&amp;', '&nbsp;XX&amp;'];
        yield 'named digits'  => ['&frac12; 1', '&frac12; X'];
    }

    /**
     * @dataProvider entities
     */
    public function test_entities_are_preserved(string $html, string $expected): void
    {
        $out = HtmlSegmenter::transformText($html, fn (string $s): string => str_replace(['1', '2'], 'X', $s));
        $this->assertSame($expected, $out);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function rawTextElements(): iterable
    {
        yield 'pre'            => ['<pre>x = 1;</pre>'];
        yield 'pre attrs'      => ['<pre class="php">x = 1;</pre>'];
        yield 'code'           => ['<code>v1.2</code>'];
        yield 'textarea'       => ['<textarea name="a">10</textarea>'];
        yield 'nested in pre'  => ['<pre><code>1</code> 2</pre>'];
        yield 'uppercase tag'  => ['<PRE>1</PRE>'];
    }

    /**
     * @dataProvider rawTextElements
     */
    public function test_raw_text_elements_are_preserved(string $block): void
    {
        $out = HtmlSegmenter::transformText($block . ' 1', fn (string $s): string => str_replace('1', 'X', $s));
        $this->assertSame($block . ' X', $out);
    }

    public function test_prefix_tag_names_are_not_mistaken_for_pre(): void
    {
        $out = HtmlSegmenter::transformText('<preview>1</preview>', fn (string $s): string => str_replace('1', 'X', $s));
        $this->assertSame('<preview>X</preview>', $out);
    }

    public function test_text_starting_with_angle_bracket_is_still_transformed(): void
    {
        // A lone "<" that never closes is text, not a tag.
        $out = HtmlSegmenter::transformText('1 < 2', fn (string $s): string => str_replace(['1', '2'], 'X', $s));
        $this->assertSame('X < X', $out);
    }

    public function test_pcre_failure_is_reported_not_swallowed(): void
    {
        $limit = ini_get('pcre.backtrack_limit');
        ini_set('pcre.backtrack_limit', '10');

        try {
            HtmlSegmenter::transformText('<script>' . str_repeat('x', 10_000), fn (string $s): string => $s);
            $this->fail('expected FormatException on PCRE failure');
        } catch (FormatException $e) {
            $this->assertSame(ErrorCode::HTML_SEGMENTATION_FAILED, $e->errorCode());
        } finally {
            ini_set('pcre.backtrack_limit', (string) $limit);
        }
    }
}
