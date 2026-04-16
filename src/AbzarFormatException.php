<?php

declare(strict_types=1);

namespace Eram\Abzar;

use Eram\Abzar\Internal\ErrorInput;
use Eram\Abzar\Validation\ErrorCode;

/**
 * Thrown by formatters when their input is unusable. Formatters are fail-fast
 * (see {@code docs/en/api-stability.md}) — they expect a caller that already
 * holds a valid value and therefore raise rather than return.
 */
final class AbzarFormatException extends AbzarException
{
    public static function forInput(ErrorCode $code, string $input, int $maxLen = 64): self
    {
        $safe = ErrorInput::truncate($input, $maxLen);
        $message = $safe === ''
            ? $code->message()
            : $code->message() . ': ' . $safe;

        return new self($code, $message);
    }
}
