<?php

declare(strict_types=1);

namespace Eram\Abzar\Text;

final class HtmlSegmenter
{
    private const PATTERN = '/((?:<script[\s>][\s\S]*?<\/script>)|(?:<style[\s>][\s\S]*?<\/style>)|(?:<!--[\s\S]*?-->)|(?:<[^>]*>))/si';

    private function __construct()
    {
    }

    /**
     * Apply $transform to every text segment of $html, leaving tags, scripts,
     * styles, and comments untouched. Returns the original input unchanged when
     * segmentation fails (PCRE error).
     *
     * @param callable(string): string $transform
     */
    public static function transformText(string $html, callable $transform): string
    {
        if ($html === '') {
            return $html;
        }

        $segments = preg_split(self::PATTERN, $html, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($segments === false) {
            return $html;
        }

        foreach ($segments as &$segment) {
            if (!isset($segment[0]) || $segment[0] !== '<') {
                $segment = $transform($segment);
            }
        }

        return implode('', $segments);
    }
}
