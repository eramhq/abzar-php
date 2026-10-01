<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Validation;

use Eram\Abzar\Validation\ErrorCode;
use Eram\Abzar\Validation\LegalId;
use PHPUnit\Framework\TestCase;

class LegalIdTest extends TestCase
{
    public function test_valid_legal_id(): void
    {
        $result = LegalId::validate('10380284790');
        $this->assertTrue($result->isValid());
    }

    public function test_persian_digit_input(): void
    {
        $result = LegalId::validate('۱۰۳۸۰۲۸۴۷۹۰');
        $this->assertTrue($result->isValid());
    }

    public function test_arabic_digit_input(): void
    {
        $result = LegalId::validate('١٠٣٨٠٢٨٤٧٩٠');
        $this->assertTrue($result->isValid());
    }

    public function test_invalid_checksum(): void
    {
        $result = LegalId::validate('10380284792');
        $this->assertFalse($result->isValid());
    }

    public function test_too_short(): void
    {
        $result = LegalId::validate('1234567');
        $this->assertFalse($result->isValid());
    }

    public function test_too_long(): void
    {
        $result = LegalId::validate('123456789012');
        $this->assertFalse($result->isValid());
    }

    public function test_empty_string(): void
    {
        $result = LegalId::validate('');
        $this->assertFalse($result->isValid());
    }

    public function test_non_numeric(): void
    {
        $result = LegalId::validate('abcdefghijk');
        $this->assertFalse($result->isValid());
    }

    public function test_all_zero_middle_rejected(): void
    {
        // Construct an ID with zeros in positions 3-8
        // 12300000089 — middle 6 positions are all zero
        $result = LegalId::validate('12300000089');
        $this->assertFalse($result->isValid());
    }

    public function test_whitespace_trimmed(): void
    {
        $result = LegalId::validate('  10380284790  ');
        $this->assertTrue($result->isValid());
    }

    public function test_from_returns_value_object(): void
    {
        $legal = LegalId::from('10380284790');
        $this->assertSame('10380284790', $legal->value());
        $this->assertSame('10380284790', (string) $legal);
    }

    public function test_from_throws_on_invalid(): void
    {
        $this->expectException(\Eram\Abzar\Exception\ValidationException::class);
        LegalId::from('10380284792');
    }

    public function test_try_from_null_on_invalid(): void
    {
        $this->assertNull(LegalId::tryFrom(''));
    }

    public function test_fake_returns_valid_id(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $id = LegalId::fake();
            $this->assertTrue(LegalId::validate($id)->isValid(), "generated $id");
        }
    }

    public function test_accepts_grouped_and_pasted_input(): void
    {
        // B4 — LegalId previously skipped the shared input cleaner.
        $this->assertTrue(LegalId::validate('103-8028-4790')->isValid());
        $this->assertTrue(LegalId::validate('1038 0284 790')->isValid());
        $this->assertTrue(LegalId::validate("\u{200F}10380284790\u{00A0}")->isValid());
    }

    /**
     * One valid ID per check digit, plus one whose weighted sum is 10 (folded
     * to check digit 0). Computed independently from the persian-tools
     * algorithm.
     *
     * @return iterable<string, array{string}>
     */
    public static function validPerCheckDigit(): iterable
    {
        yield 'check 0'          => ['71049746500'];
        yield 'check 1'          => ['62527601891'];
        yield 'check 2'          => ['83016613182'];
        yield 'check 3'          => ['94821993513'];
        yield 'check 4'          => ['75291703424'];
        yield 'check 5'          => ['60313721595'];
        yield 'check 6'          => ['52601815906'];
        yield 'check 7'          => ['60913909967'];
        yield 'check 8'          => ['79754323198'];
        yield 'check 9'          => ['03082462819'];
        yield 'sum 10 folds to 0' => ['22330792440'];
    }

    /**
     * @dataProvider validPerCheckDigit
     */
    public function test_valid_for_every_check_digit(string $id): void
    {
        $this->assertTrue(LegalId::validate($id)->isValid());

        // Any other final digit must fail the checksum.
        $wrong = substr($id, 0, 10) . (((int) $id[10] + 1) % 10);
        $this->assertSame([ErrorCode::LEGAL_ID_INVALID_CHECKSUM], LegalId::validate($wrong)->errorCodes());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function digitsWithStrayLetter(): iterable
    {
        yield 'leading letter'  => ['x10380284790'];
        yield 'trailing letter' => ['10380284790x'];
    }

    /**
     * @dataProvider digitsWithStrayLetter
     */
    public function test_stray_letter_is_wrong_length(string $input): void
    {
        $this->assertSame([ErrorCode::LEGAL_ID_WRONG_LENGTH], LegalId::validate($input)->errorCodes());
    }

    public function test_middle_zeros_reported_by_code(): void
    {
        // Digits 4–9 are zero and the checksum is otherwise valid; digits 3
        // and 10 are not zero, so only the exact substr(3, 6) window matches.
        $this->assertSame([ErrorCode::LEGAL_ID_MIDDLE_ZEROS], LegalId::validate('12300000045')->errorCodes());
    }
}
