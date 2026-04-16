<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

final class ValidationResult implements \JsonSerializable, \Stringable
{
    /**
     * @param list<string>         $errors
     * @param array<string, mixed> $details
     */
    private function __construct(
        private readonly bool  $valid,
        private readonly array $errors = [],
        private readonly array $details = [],
    ) {}

    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @return list<string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return $this->details;
    }

    /**
     * @param array<string, mixed> $details
     */
    public static function success(array $details = []): self
    {
        return new self(true, [], $details);
    }

    /**
     * @param string|list<string>  $errors
     * @param array<string, mixed> $details
     */
    public static function failure(string|array $errors, array $details = []): self
    {
        return new self(false, (array) $errors, $details);
    }

    /**
     * @return array{valid: bool, errors: list<string>, details: array<string, mixed>}
     */
    public function jsonSerialize(): array
    {
        return [
            'valid'   => $this->valid,
            'errors'  => $this->errors,
            'details' => $this->details,
        ];
    }

    public function __toString(): string
    {
        if ($this->valid) {
            return 'valid';
        }

        return $this->errors === [] ? 'invalid' : implode('; ', $this->errors);
    }
}
