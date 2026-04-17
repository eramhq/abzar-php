<?php

declare(strict_types=1);

namespace Eram\Abzar;

use Eram\Abzar\Validation\ErrorCode;

/**
 * Raised when an abzar feature is opted into but its required runtime
 * prerequisite isn't available — e.g. {@see \Eram\Abzar\Text\CharNormalizer::$normalizeToNfc}
 * without {@code ext-intl}. Carries the canonical {@see ErrorCode} so it can
 * be caught uniformly via {@see AbzarException}.
 */
final class AbzarEnvironmentException extends AbzarException
{
    public static function missing(ErrorCode $code, string $detail): self
    {
        return new self($code, $code->message() . ': ' . $detail);
    }
}
