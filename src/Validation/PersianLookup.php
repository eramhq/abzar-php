<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

/**
 * Shared {@code fromPersian()} implementation for the domain enums. Implementers
 * provide {@see persianName()} (canonical display form) and may override
 * {@see persianAliases()} (extra surface spellings) and {@see normalizeInput()}
 * (input canonicalization applied both when building the index and when probing).
 *
 * @internal Used by Bank / Operator / Province. Not part of the public surface.
 */
trait PersianLookup
{
    public static function fromPersian(string $name): ?self
    {
        /** @var array<string, array<string, self>> $indexes */
        static $indexes = [];

        $index = $indexes[self::class] ??= self::buildIndex();

        if (isset($index[$name])) {
            return $index[$name];
        }

        $normalized = static::normalizeInput($name);

        return $index[$normalized] ?? null;
    }

    /**
     * @return array<string, self>
     */
    private static function buildIndex(): array
    {
        $index = [];

        foreach (self::cases() as $case) {
            $canonical                                 = $case->persianName();
            $index[$canonical]                         = $case;
            $index[static::normalizeInput($canonical)] = $case;
        }

        foreach (static::persianAliases() as $alias => $case) {
            $index[$alias] = $case;
        }

        return $index;
    }

    /**
     * @return array<string, self>
     */
    protected static function persianAliases(): array
    {
        return [];
    }

    protected static function normalizeInput(string $name): string
    {
        return $name;
    }

    abstract public function persianName(): string;
}
