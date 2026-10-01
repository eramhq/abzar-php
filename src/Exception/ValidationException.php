<?php

declare(strict_types=1);

namespace Eram\Abzar\Exception;

use Eram\Abzar\Validation\ErrorCode;
use Eram\Abzar\Validation\ValidationResult;

/**
 * Thrown when a value-object constructor (e.g. {@code NationalId::from()})
 * rejects its input, and by the {@code ::fake()} generators on a bad pinned
 * argument ({@see ErrorCode::FAKE_INVALID_ARGUMENT}). The underlying {@see ValidationResult} is exposed for
 * callers that want the full error list.
 */
final class ValidationException extends AbzarException
{
    public function __construct(
        private readonly ValidationResult $result,
        ErrorCode $errorCode,
        ?string $message = null,
    ) {
        parent::__construct($errorCode, $message);
    }

    public function result(): ValidationResult
    {
        return $this->result;
    }

    /**
     * Wrap a result in an exception. The code is the first error code, else
     * the first warning code, else {@see ErrorCode::VALIDATION_FAILED} — the
     * last covers results built from plain-string errors (or a valid result
     * passed by mistake), so this factory never throws itself.
     */
    public static function fromResult(ValidationResult $result): self
    {
        $code = $result->errorCodes()[0]
             ?? $result->warningCodes()[0]
             ?? ErrorCode::VALIDATION_FAILED;

        $message = (string) $result;
        if ($message === 'valid' || $message === 'invalid') {
            $message = $code->message();
        }

        return new self($result, $code, $message);
    }

    /**
     * Raised by the {@code ::fake()} fixture generators when a pinned argument
     * (BIN, bank code, city code, plate type, …) can't produce a valid value.
     * $detail is a developer-facing English explanation.
     */
    public static function forFakeArgument(string $detail): self
    {
        $code = ErrorCode::FAKE_INVALID_ARGUMENT;

        return new self(ValidationResult::invalid($code), $code, $code->message() . ': ' . $detail);
    }
}
