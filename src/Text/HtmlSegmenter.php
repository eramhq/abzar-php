<?php

declare(strict_types=1);

namespace Eram\Abzar\Text;

use Eram\Abzar\Exception\FormatException;
use Eram\Abzar\Validation\ErrorCode;

final class HtmlSegmenter
{
    /**
     * One capturing group so {@see preg_split()} with DELIM_CAPTURE returns
     * protected segments at odd indexes. Protected, in order of precedence:
     * raw-text / code elements with their content, comments, any other tag,
     * and character references ({@code &#8204;}, {@code &#x200C;}, {@code &nbsp;}).
     * ASCII-only, so it runs byte-wise and never trips over malformed UTF-8.
     */
    private const PATTERN = '/('
        . '<script[\s>][\s\S]*?<\/script\s*>'
        . '|<style[\s>][\s\S]*?<\/style\s*>'
        . '|<pre[\s>][\s\S]*?<\/pre\s*>'
        . '|<code[\s>][\s\S]*?<\/code\s*>'
        . '|<textarea[\s>][\s\S]*?<\/textarea\s*>'
        . '|<!--[\s\S]*?-->'
        . '|<[^>]*>'
        . '|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]*);'
        . ')/i';

    private function __construct()
    {
    }

    /**
     * Apply $transform to every text segment of $html, leaving tags, character
     * references, comments, and the content of script / style / pre / code /
     * textarea elements untouched.
     *
     * @param callable(string): string $transform
     *
     * @throws FormatException when PCRE fails to segment the input (e.g. the
     *                         backtrack limit is hit on a huge unterminated block).
     */
    public static function transformText(string $html, callable $transform): string
    {
        if ($html === '') {
            return $html;
        }

        $segments = preg_split(self::PATTERN, $html, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($segments === false) {
            throw new FormatException(
                ErrorCode::HTML_SEGMENTATION_FAILED,
                ErrorCode::HTML_SEGMENTATION_FAILED->message() . ': ' . preg_last_error_msg(),
            );
        }

        foreach ($segments as $i => &$segment) {
            if ($i % 2 === 0 && $segment !== '') {
                $segment = $transform($segment);
            }
        }
        unset($segment);

        return implode('', $segments);
    }
}
