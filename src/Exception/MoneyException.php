<?php

declare(strict_types=1);

namespace Eram\Abzar\Exception;

use Eram\Abzar\Internal\ErrorInput;
use Eram\Abzar\Validation\ErrorCode;

/**
 * Thrown by {@see \Eram\Abzar\Money\Amount} when an operation would leave the
 * valid range — a negative amount ({@see ErrorCode::AMOUNT_NEGATIVE}) or one
 * past {@code PHP_INT_MAX} rials ({@see ErrorCode::AMOUNT_OVERFLOW}).
 */
final class MoneyException extends AbzarException
{
    public static function forInput(ErrorCode $code, string $input, int $maxLen = 64): self
    {
        $safe = ErrorInput::truncate($input, $maxLen);

        return new self($code, $safe === '' ? $code->message() : $code->message() . ': ' . $safe);
    }
}
