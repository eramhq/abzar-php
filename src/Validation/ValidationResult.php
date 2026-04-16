<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

final class ValidationResult implements \JsonSerializable, \Stringable
{
    /**
     * @param list<string>         $errors
     * @param list<?ErrorCode>     $errorCodes
     * @param list<string>         $warnings
     * @param list<?ErrorCode>     $warningCodes
     * @param array<string, mixed> $details
     */
    private function __construct(
        private readonly bool  $valid,
        private readonly array $errors = [],
        private readonly array $errorCodes = [],
        private readonly array $warnings = [],
        private readonly array $warningCodes = [],
        private readonly array $details = [],
    ) {
    }

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
     * @return list<ErrorCode>
     */
    public function errorCodes(): array
    {
        return self::pruneNulls($this->errorCodes);
    }

    /**
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    /**
     * @return list<ErrorCode>
     */
    public function warningCodes(): array
    {
        return self::pruneNulls($this->warningCodes);
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return $this->details;
    }

    public function bank(): ?Bank
    {
        return $this->detailEnum('bank', Bank::fromPersian(...));
    }

    public function operator(): ?Operator
    {
        return $this->detailEnum('operator', Operator::fromPersian(...));
    }

    public function province(): ?Province
    {
        return $this->detailEnum('province', Province::fromPersian(...));
    }

    /**
     * @param array<string, mixed>                    $details
     * @param list<string|ErrorCode>|string|ErrorCode $warnings
     */
    public static function success(array $details = [], string|ErrorCode|array $warnings = []): self
    {
        [$warnMsgs, $warnCodes] = self::resolve($warnings);

        return new self(true, [], [], $warnMsgs, $warnCodes, $details);
    }

    /**
     * @param list<string|ErrorCode>|string|ErrorCode $errors
     * @param array<string, mixed>                    $details
     * @param list<string|ErrorCode>|string|ErrorCode $warnings
     */
    public static function failure(
        string|ErrorCode|array $errors,
        array $details = [],
        string|ErrorCode|array $warnings = [],
    ): self {
        [$errMsgs, $errCodes]   = self::resolve($errors);
        [$warnMsgs, $warnCodes] = self::resolve($warnings);

        return new self(false, $errMsgs, $errCodes, $warnMsgs, $warnCodes, $details);
    }

    /**
     * @return array{
     *     valid: bool,
     *     errors: list<string>,
     *     error_codes: list<string>,
     *     details: array<string, mixed>,
     *     warnings?: list<string>,
     *     warning_codes?: list<string>
     * }
     */
    public function jsonSerialize(): array
    {
        $payload = [
            'valid'       => $this->valid,
            'errors'      => $this->errors,
            'error_codes' => self::codeValues($this->errorCodes),
            'details'     => $this->details,
        ];

        if ($this->warnings !== []) {
            $payload['warnings']      = $this->warnings;
            $payload['warning_codes'] = self::codeValues($this->warningCodes);
        }

        return $payload;
    }

    public function __toString(): string
    {
        if ($this->valid) {
            return 'valid';
        }

        return $this->errors === [] ? 'invalid' : implode('; ', $this->errors);
    }

    /**
     * @template T of \BackedEnum
     * @param callable(string): ?T $factory
     * @return T|null
     */
    private function detailEnum(string $key, callable $factory): ?object
    {
        $name = $this->details[$key] ?? null;

        return is_string($name) ? $factory($name) : null;
    }

    /**
     * @param list<string|ErrorCode>|string|ErrorCode $input
     * @return array{0: list<string>, 1: list<?ErrorCode>}
     */
    private static function resolve(string|ErrorCode|array $input): array
    {
        $items    = is_array($input) ? $input : [$input];
        $messages = [];
        $codes    = [];

        foreach ($items as $item) {
            if ($item instanceof ErrorCode) {
                $messages[] = $item->message();
                $codes[]    = $item;
            } else {
                $messages[] = $item;
                $codes[]    = null;
            }
        }

        return [$messages, $codes];
    }

    /**
     * @param list<?ErrorCode> $codes
     * @return list<ErrorCode>
     */
    private static function pruneNulls(array $codes): array
    {
        return array_values(array_filter($codes, static fn (?ErrorCode $c): bool => $c !== null));
    }

    /**
     * @param list<?ErrorCode> $codes
     * @return list<string>
     */
    private static function codeValues(array $codes): array
    {
        $out = [];
        foreach ($codes as $code) {
            if ($code !== null) {
                $out[] = $code->value;
            }
        }

        return $out;
    }
}
