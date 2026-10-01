<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Fixtures;

use Eram\Abzar\Exception\AbzarException;
use PHPUnit\Framework\TestCase;

/**
 * Base for the persian-tools contract tests. Vectors are hand-lifted from the
 * specs vendored in {@code tests/fixtures/persian-tools/}, each citing its
 * {@code spec.ts:line}; every test skips when that tree hasn't been pulled
 * (`composer fixtures:pull`).
 *
 * Deliberate differences live in each subclass's {@code divergences()}
 * registry as {@code [api, input, upstream result, abzar result, reason]} and
 * assert abzar's value, so a behaviour change on either side surfaces here.
 * docs/en/persian-tools-parity.md lists them.
 */
abstract class PersianToolsFixtureTestCase extends TestCase
{
    protected const FIXTURES_DIR = __DIR__ . '/../../fixtures/persian-tools';

    protected function setUp(): void
    {
        if (!is_file(self::FIXTURES_DIR . '/SHA')) {
            self::markTestSkipped('persian-tools fixtures not pulled. Run `composer fixtures:pull`.');
        }
    }

    /**
     * Run $call, mapping an {@see AbzarException} to {@code "throws <code>"}
     * so registry entries can record a rejection as a plain value.
     */
    protected static function outcome(\Closure $call): mixed
    {
        try {
            return $call();
        } catch (AbzarException $e) {
            return 'throws ' . $e->errorCode()->value;
        }
    }

    protected static function assertDivergence(mixed $upstream, mixed $abzar, mixed $actual, string $reason): void
    {
        self::assertNotSame($upstream, $abzar, 'Registry entry no longer diverges; move it to the parity vectors.');
        self::assertSame($abzar, $actual, $reason);
    }
}
