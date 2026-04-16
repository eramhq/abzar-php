<?php

declare(strict_types=1);

namespace Eram\Abzar\Data;

/**
 * Lazy loaders for the bundled lookup tables.
 */
final class DataSources
{
    /** Upstream source the bundled tables were copied from. */
    public const SOURCE = 'persian-tools@5.0.0-beta.0';

    /** Date of the last data refresh (YYYY-MM-DD, UTC). */
    public const UPDATED_AT = '2026-04-16';

    private function __construct()
    {
    }

    /**
     * @return array<string, array{city: ?string, province: ?string}>
     */
    public static function nationalIdCityCodes(): array
    {
        /** @var array<string, array{city: ?string, province: ?string}> */
        return self::load('NationalIdCityCodes.php');
    }

    /**
     * @return array<string, string>
     */
    public static function cardBanks(): array
    {
        /** @var array<string, string> */
        return self::load('CardBanks.php');
    }

    /**
     * @return array<string, string>
     */
    public static function ibanBanks(): array
    {
        /** @var array<string, string> */
        return self::load('IbanBanks.php');
    }

    /**
     * @return array<string, string>
     */
    public static function phoneOperators(): array
    {
        /** @var array<string, string> */
        return self::load('PhoneOperators.php');
    }

    /**
     * @return array<string, array{province: string, city: string}>
     */
    public static function phoneAreaCodes(): array
    {
        /** @var array<string, array{province: string, city: string}> */
        return self::load('PhoneAreaCodes.php');
    }

    /**
     * @return array<array-key, mixed>
     */
    private static function load(string $file): array
    {
        /** @var array<string, array<array-key, mixed>> $cache */
        static $cache = [];

        return $cache[$file] ??= require __DIR__ . '/' . $file;
    }
}
