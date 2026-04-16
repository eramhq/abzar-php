<?php

declare(strict_types=1);

namespace Eram\Abzar\Benchmarks;

use Eram\Abzar\Validation\NationalId;

/**
 * @BeforeMethods({"warmup"})
 * @Revs(1000)
 * @Iterations(5)
 */
final class NationalIdBench
{
    private const VALID    = '0499370899';
    private const INVALID  = '0499370898';

    public function warmup(): void
    {
        NationalId::validate(self::VALID);
    }

    public function benchValid(): void
    {
        NationalId::validate(self::VALID);
    }

    public function benchInvalidChecksum(): void
    {
        NationalId::validate(self::INVALID);
    }

    public function benchWrongLength(): void
    {
        NationalId::validate('12345');
    }
}
