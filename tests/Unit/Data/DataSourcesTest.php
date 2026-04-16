<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Data;

use Eram\Abzar\Data\DataSources;
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
}
