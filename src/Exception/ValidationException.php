<?php

declare(strict_types=1);

namespace Eram\Abzar\Exception;

use Eram\Abzar\Validation\ErrorCode;
use Eram\Abzar\Validation\ValidationResult;

/**
 * Thrown when a value-object constructor (e.g. {@code NationalId::from()})
 * rejects its input. The underlying {@see ValidationResult} is exposed for
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

    public static function fromResult(ValidationResult $result): self
    {
        $code = $result->errorCodes()[0]
             ?? $result->warningCodes()[0]
             ?? null;

        if ($code === null) {
            throw new \LogicException(
                'ValidationException cannot be constructed from a ValidationResult without at least one ErrorCode.'
            );
        }

        return new self($result, $code, (string) $result);
    }
}
