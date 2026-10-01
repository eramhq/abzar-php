<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Fixtures;

use Eram\Abzar\Validation\LegalId;

/**
 * verifyIranianLegalId.spec.ts ↔ {@see LegalId::validate()}. Every vector
 * matches, so there is no divergence registry. Upstream also accepts numbers;
 * abzar takes the same digits as a string.
 */
final class LegalIdContractTest extends PersianToolsFixtureTestCase
{
    /**
     * @dataProvider vectors
     */
    public function test_legal_id_parity(string $id, bool $upstreamValid): void
    {
        self::assertSame($upstreamValid, LegalId::validate($id)->isValid());
    }

    /** @return iterable<string, array{string, bool}> */
    public static function vectors(): iterable
    {
        // verifyIranianLegalId.spec.ts:5-11
        yield 'short, middle zeros (int) :5'    => ['123000000', false];
        yield 'short, middle zeros (string) :6' => ['123000000', false];
        yield 'all ones (int) :7'               => ['11111111111', false];
        yield 'ten ones (string) :8'            => ['1111111111', false];
        yield 'bad checksum :9'                 => ['10380284792', false];
        yield 'bad checksum :10'                => ['10380285692', false];
        yield 'bad checksum, leading zero :11'  => ['09748208301', false];
        // verifyIranianLegalId.spec.ts:13-14
        yield 'valid (int) :13'                 => ['10380284790', true];
        yield 'valid (string) :14'              => ['10380284790', true];
    }
}
