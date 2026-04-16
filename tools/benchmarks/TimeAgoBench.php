<?php

declare(strict_types=1);

namespace Eram\Abzar\Benchmarks;

use Eram\Abzar\Format\TimeAgo;

/**
 * @Revs(1000)
 * @Iterations(5)
 */
final class TimeAgoBench
{
    private const NOW = 1_800_000_000;

    public function benchMinutesAgo(): void
    {
        TimeAgo::format(self::NOW - 300, self::NOW);
    }

    public function benchDaysAgo(): void
    {
        TimeAgo::format(self::NOW - 86400 * 3, self::NOW);
    }

    public function benchYearsAgo(): void
    {
        TimeAgo::format(self::NOW - 31_536_000 * 4, self::NOW);
    }
}
