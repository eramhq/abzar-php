<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation\Details;

/**
 * Marker interface implemented by every {@code *Details} readonly DTO
 * returned from {@see \Eram\Abzar\Validation\ValidationResult::detail()}.
 *
 * The interface seals the detail payload type so callers can narrow from
 * {@code ?ValidationDetail} to a concrete DTO without annotating an unrelated
 * {@see \JsonSerializable} return type.
 */
interface ValidationDetail extends \JsonSerializable
{
}
