<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Docs;

use Eram\Abzar\Validation\ErrorCode;
use PHPUnit\Framework\TestCase;

/**
 * Keeps both localized error-codes.md references complete: every {@see ErrorCode} case needs a
 * table row that starts with its value and current Persian message. Mirrors
 * the completeness check in ErrorCodeMessageSnapshotTest.
 */
final class ErrorCodeDocsTest extends TestCase
{
    /** @dataProvider locales */
    public function test_every_case_has_a_row_with_its_message(string $locale): void
    {
        $path = __DIR__ . '/../../../docs/' . $locale . '/error-codes.md';
        if ($locale === 'fa' && !is_file($path)) {
            self::markTestSkipped('Persian error-code translation is missing; docs:check reports it.');
        }
        self::assertFileExists($path);
        $page = (string) file_get_contents($path);

        $missing = [];
        foreach (ErrorCode::cases() as $case) {
            if (!str_contains($page, '| `' . $case->value . '` | ' . $case->message() . ' |')) {
                $missing[] = $case->value;
            }
        }

        self::assertSame([], $missing, 'add or update these rows in docs/' . $locale . '/error-codes.md');
    }
    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'English' => ['en'];
        yield 'Persian' => ['fa'];
    }
}
