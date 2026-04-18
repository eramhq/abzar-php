<?php

declare(strict_types=1);

namespace Eram\Abzar\Text;

use Eram\Abzar\Exception\EnvironmentException;
use Eram\Abzar\Validation\ErrorCode;

/**
 * Thin wrapper around {@see \Collator} for Persian-correct string ordering.
 * Native PHP {@see sort()} compares by code point, placing Arabic / Persian
 * letters in historical Unicode order rather than alphabetical — sorting
 * Persian strings the obvious way produces obviously wrong results.
 *
 * This class requires {@code ext-intl}. Every entry point raises
 * {@see EnvironmentException} (with {@see ErrorCode::ENV_MISSING_EXT_INTL})
 * at construction when the extension is missing.
 */
final class PersianCollator
{
    private readonly \Collator $collator;

    public function __construct(string $locale = 'fa_IR')
    {
        if (!class_exists(\Collator::class)) {
            throw EnvironmentException::missing(
                ErrorCode::ENV_MISSING_EXT_INTL,
                'PersianCollator requires ext-intl. Install the intl extension.',
            );
        }

        $this->collator = new \Collator($locale);
    }

    public function compare(string $a, string $b): int
    {
        return (int) $this->collator->compare($a, $b);
    }

    /**
     * @param array<array-key, string> $values
     * @return array<array-key, string>
     */
    public function sort(array $values): array
    {
        $this->collator->sort($values);

        return $values;
    }

    /**
     * Stable sort by a caller-supplied key extractor.
     *
     * @template T
     * @param iterable<T> $items
     * @param callable(T): string $key
     * @return list<T>
     */
    public function sortBy(iterable $items, callable $key): array
    {
        $buffered = [];
        foreach ($items as $item) {
            $buffered[] = [$key($item), $item];
        }

        usort($buffered, fn (array $x, array $y): int => $this->compare($x[0], $y[0]));

        /** @var list<T> */
        return array_column($buffered, 1);
    }

}
