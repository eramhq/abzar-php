<?php

declare(strict_types=1);

namespace Eram\Abzar\Internal;

use Eram\Abzar\Digits\DigitConverter;

/**
 * Shared engine behind the validators' {@code ::extractAll()} methods: fold
 * Persian / Arabic digits, find candidate runs with a validator-specific
 * pattern, and keep the ones the validator's {@code ::tryFrom()} accepts —
 * in left-to-right order.
 *
 * @internal Not covered by BC guarantees; do not depend on from outside abzar.
 */
final class Extractor
{
    private function __construct()
    {
    }

    /**
     * @template T of object
     *
     * @param string               $pattern a {@code /u} regex whose full match is one candidate
     * @param callable(string): ?T $tryFrom
     *
     * @return list<T>
     */
    public static function all(string $text, string $pattern, callable $tryFrom): array
    {
        $english = DigitConverter::toEnglish($text);

        // Malformed UTF-8 makes a /u pattern fail outright; scrub the bad bytes
        // so the well-formed parts of the text are still searched.
        if (preg_match_all($pattern, $english, $matches) === false) {
            preg_match_all($pattern, mb_scrub($english, 'UTF-8'), $matches);
        }

        $out = [];
        foreach ($matches[0] ?? [] as $candidate) {
            $vo = $tryFrom($candidate);
            if ($vo !== null) {
                $out[] = $vo;
            }
        }

        return $out;
    }
}
