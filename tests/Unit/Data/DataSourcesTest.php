<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Data;

use Eram\Abzar\Data\DataSources;
use Eram\Abzar\Validation\Bank;
use Eram\Abzar\Validation\Operator;
use Eram\Abzar\Validation\PlateType;
use Eram\Abzar\Validation\Province;
use PHPUnit\Framework\TestCase;

final class DataSourcesTest extends TestCase
{
    public function test_source_constants(): void
    {
        self::assertNotSame('', DataSources::SOURCE);
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', DataSources::UPDATED_AT);
    }

    public function test_national_id_city_codes_loads(): void
    {
        $data = DataSources::nationalIdCityCodes();
        self::assertGreaterThan(400, count($data));
        self::assertArrayHasKey('001', $data);
        self::assertSame('تهران', $data['001']['province']);
    }

    public function test_card_banks_loads(): void
    {
        $data = DataSources::cardBanks();
        self::assertContains('بانک ملی ایران', $data);
    }

    public function test_iban_banks_loads(): void
    {
        $data = DataSources::ibanBanks();
        self::assertContains('بانک ملی ایران', $data);
    }

    public function test_phone_operators_loads(): void
    {
        $data = DataSources::phoneOperators();
        self::assertContains('همراه اول', $data);
    }

    public function test_loaders_memoize(): void
    {
        self::assertSame(DataSources::cardBanks(), DataSources::cardBanks());
    }

    public function test_plate_tables_load(): void
    {
        self::assertSame(['تهران'], DataSources::plateCodes()['77']);
        self::assertSame('government', DataSources::plateLetters()['الف']);
    }

    /**
     * Every Persian bank / operator / province name stored in a data table must
     * resolve through the matching enum's fromPersian(). Catches the class of
     * drift where a table spells a name differently from the enum (B3).
     */
    public function test_every_bundled_name_resolves_to_an_enum(): void
    {
        $unresolved = [];

        foreach (DataSources::cardBanks() as $bin => $name) {
            if (Bank::fromPersian($name) === null) {
                $unresolved[] = "cardBanks[$bin] $name";
            }
        }
        foreach (DataSources::ibanBanks() as $code => $name) {
            if (Bank::fromPersian($name) === null) {
                $unresolved[] = "ibanBanks[$code] $name";
            }
        }
        foreach (DataSources::phoneOperators() as $prefix => $name) {
            if (Operator::fromPersian($name) === null) {
                $unresolved[] = "phoneOperators[$prefix] $name";
            }
        }
        foreach (DataSources::phoneAreaCodes() as $code => $row) {
            if (Province::fromPersian($row['province']) === null) {
                $unresolved[] = "phoneAreaCodes[$code] {$row['province']}";
            }
        }
        foreach (DataSources::nationalIdCityCodes() as $code => $row) {
            if ($row['province'] !== null && Province::fromPersian($row['province']) === null) {
                $unresolved[] = "nationalIdCityCodes[$code] {$row['province']}";
            }
        }
        foreach (DataSources::plateCodes() as $code => $provinces) {
            foreach ($provinces as $name) {
                if (Province::fromPersian($name) === null) {
                    $unresolved[] = "plateCodes[$code] $name";
                }
            }
        }

        self::assertSame([], $unresolved);
    }

    public function test_every_plate_letter_maps_to_a_known_type(): void
    {
        foreach (DataSources::plateLetters() as $letter => $type) {
            self::assertNotNull(PlateType::tryFrom($type), "plateLetters[$letter]");
            self::assertNotSame(PlateType::OTHER->value, $type, "plateLetters[$letter]");
        }
    }

    public function test_karaj_area_code_is_alborz(): void
    {
        self::assertSame('البرز', DataSources::phoneAreaCodes()['026']['province']);
    }
}
