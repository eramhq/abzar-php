<?php

declare(strict_types=1);

namespace Eram\Abzar\Exception;

use Eram\Abzar\Validation\ErrorCode;

/**
 * Root exception for every failure the library raises. Always carries an
 * {@see ErrorCode} so callers can {@code catch (AbzarException $e)} and
 * dispatch on {@code $e->errorCode()} without string-matching messages.
 */
abstract class AbzarException extends \RuntimeException
{
    public function __construct(
        private readonly ErrorCode $errorCode,
        ?string $message = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message ?? $errorCode->message(), 0, $previous);
    }

    public function errorCode(): ErrorCode
    {
        return $this->errorCode;
    }
}
