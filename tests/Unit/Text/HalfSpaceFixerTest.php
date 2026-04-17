<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Text;

use Eram\Abzar\Text\HalfSpaceFixer;
use PHPUnit\Framework\TestCase;

final class HalfSpaceFixerTest extends TestCase
{
    public function test_prefix_mi_binds_with_zwnj(): void
    {
        $this->assertSame("می\u{200C}روم", HalfSpaceFixer::fix('می روم'));
    }

    public function test_prefix_nemi_binds_with_zwnj(): void
    {
        $this->assertSame("نمی\u{200C}رود", HalfSpaceFixer::fix('نمی رود'));
    }

    public function test_suffix_ha_binds_with_zwnj(): void
    {
        $this->assertSame("خانه\u{200C}ها", HalfSpaceFixer::fix('خانه ها'));
    }

    public function test_suffix_tarin_binds_with_zwnj(): void
    {
        $this->assertSame("بزرگ\u{200C}ترین", HalfSpaceFixer::fix('بزرگ ترین'));
    }

    public function test_suffix_ter_binds_with_zwnj(): void
    {
        $this->assertSame("بزرگ\u{200C}تر", HalfSpaceFixer::fix('بزرگ تر'));
    }

    public function test_suffix_before_punctuation(): void
    {
        $this->assertSame("خانه\u{200C}ها.", HalfSpaceFixer::fix('خانه ها.'));
    }

    public function test_mixed_prefix_and_suffix(): void
    {
        $this->assertSame(
            "می\u{200C}آیند و خانه\u{200C}ها را می\u{200C}بینند",
            HalfSpaceFixer::fix('می آیند و خانه ها را می بینند'),
        );
    }

    public function test_does_not_touch_standalone_non_persian(): void
    {
        $this->assertSame('hello world', HalfSpaceFixer::fix('hello world'));
    }

    public function test_does_not_double_apply(): void
    {
        $already = "می\u{200C}روم";
        $this->assertSame($already, HalfSpaceFixer::fix($already));
    }

    public function test_mi_before_noun_glues_by_design(): void
    {
        // Locks the intentional broadness of می/نمی — see HalfSpaceFixer docblock before tightening.
        $this->assertSame("این می\u{200C}کتاب است", HalfSpaceFixer::fix('این می کتاب است'));
    }
}
