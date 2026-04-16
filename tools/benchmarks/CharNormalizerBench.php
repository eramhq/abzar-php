<?php

declare(strict_types=1);

namespace Eram\Abzar\Benchmarks;

use Eram\Abzar\Text\CharNormalizer;

/**
 * @BeforeMethods({"setUp"})
 * @Revs(500)
 * @Iterations(5)
 */
final class CharNormalizerBench
{
    private CharNormalizer $normalizer;
    private string $small;
    private string $large;

    public function setUp(): void
    {
        $this->normalizer = new CharNormalizer(tehMarbuta: true, foldHamza: true, stripTashkeel: true);
        $this->small = str_repeat('سلام كتابي ٠١٢ ', 100);        // ~10 KB
        $this->large = '<div>' . str_repeat('<p>كتابي عربي</p>', 5000) . '</div>';
    }

    public function benchNormalizeSmall(): void
    {
        $this->normalizer->normalize($this->small);
    }

    public function benchNormalizeContentLarge(): void
    {
        $this->normalizer->normalizeContent($this->large);
    }
}
