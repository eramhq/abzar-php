<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Docs;

use Eram\Abzar\Validation\ErrorCode;
use PHPUnit\Framework\TestCase;

/**
 * Keeps docs/en/error-codes.md complete: every {@see ErrorCode} case needs a
 * table row that starts with its value and current Persian message. Mirrors
 * the completeness check in ErrorCodeMessageSnapshotTest.
 */
final class ErrorCodeDocsTest extends TestCase
{
    private const PAGE = __DIR__ . '/../../../docs/en/error-codes.md';

    public function test_every_case_has_a_row_with_its_message(): void
    {
        self::assertFileExists(self::PAGE);
        $page = (string) file_get_contents(self::PAGE);

        $missing = [];
        foreach (ErrorCode::cases() as $case) {
            if (!str_contains($page, '| `' . $case->value . '` | ' . $case->message() . ' |')) {
                $missing[] = $case->value;
            }
        }

        self::assertSame([], $missing, 'add or update these rows in docs/en/error-codes.md');
    }
}
