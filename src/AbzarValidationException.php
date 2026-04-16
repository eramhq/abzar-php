<?php

declare(strict_types=1);

namespace Eram\Abzar;

use Eram\Abzar\Validation\ErrorCode;
use Eram\Abzar\Validation\ValidationResult;

/**
 * Thrown when a value-object constructor (e.g. {@code NationalId::from()})
 * rejects its input. The underlying {@see ValidationResult} is exposed for
 * callers that want the full error list.
 */
final class AbzarValidationException extends AbzarException
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
        $codes = $result->errorCodes();
        $code  = $codes[0] ?? null;

        if ($code === null) {
            throw new \LogicException(
                'AbzarValidationException cannot be constructed from a ValidationResult without at least one ErrorCode.'
            );
        }

        return new self($result, $code, (string) $result);
    }
}
