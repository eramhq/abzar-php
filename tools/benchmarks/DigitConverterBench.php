<?php

declare(strict_types=1);

namespace Eram\Abzar\Benchmarks;

use Eram\Abzar\Digits\DigitConverter;

/**
 * @BeforeMethods({"setUp"})
 * @Revs(200)
 * @Iterations(5)
 */
final class DigitConverterBench
{
    private string $englishPayload;
    private string $persianPayload;

    public function setUp(): void
    {
        $this->englishPayload = str_repeat('lorem ipsum 0123456789 dolor sit amet ', 10_000); // ~380KB
        $this->persianPayload = str_repeat('فارسی ۰۱۲۳۴۵۶۷۸۹ متن ', 10_000);
    }

    public function benchToPersian(): void
    {
        DigitConverter::toPersian($this->englishPayload);
    }

    public function benchToEnglish(): void
    {
        DigitConverter::toEnglish($this->persianPayload);
    }
}
