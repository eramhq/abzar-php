<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\Validation\Details\ValidationDetail;

/**
 * Outcome of a {@code ::validate()} call — always returned, never thrown.
 *
 * Construct via the three named factories:
 *  * {@see self::valid()} — successful validation with a typed detail DTO.
 *  * {@see self::validWithWarnings()} — success but with non-fatal warnings.
 *  * {@see self::invalid()} — rejection, with one or more {@see ErrorCode}s.
 *
 * Detail payloads are per-validator {@code readonly} DTOs implementing
 * {@see ValidationDetail} under {@see \Eram\Abzar\Validation\Details}.
 * Call {@see self::detail()} to access.
 */
final class ValidationResult implements \JsonSerializable, \Stringable
{
    /**
     * @param list<string>       $errors
     * @param list<?ErrorCode>   $errorCodes
     * @param list<string>       $warnings
     * @param list<?ErrorCode>   $warningCodes
     */
    private function __construct(
        private readonly bool              $valid,
        private readonly array             $errors = [],
        private readonly array             $errorCodes = [],
        private readonly array             $warnings = [],
        private readonly array             $warningCodes = [],
        private readonly ?ValidationDetail $detail = null,
    ) {
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * True when the result is valid AND carries no warnings — the contract
     * value-object constructors use to decide whether the result can yield
     * a VO (warning-bearing results can't, because the VO shape has no slot
     * to represent "valid but uncertain").
     */
    public function isStrictlyValid(): bool
    {
        return $this->valid && $this->warnings === [];
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

    public function detail(): ?ValidationDetail
    {
        return $this->detail;
    }

    /**
     * Successful validation.
     */
    public static function valid(?ValidationDetail $detail = null): self
    {
        return new self(valid: true, detail: $detail);
    }

    /**
     * Successful validation with non-fatal warnings.
     *
     * @param list<string|ErrorCode>|string|ErrorCode $warnings
     */
    public static function validWithWarnings(
        string|ErrorCode|array $warnings,
        ?ValidationDetail $detail = null,
    ): self {
        [$msgs, $codes] = self::resolve($warnings);

        return new self(
            valid: true,
            warnings: $msgs,
            warningCodes: $codes,
            detail: $detail,
        );
    }

    /**
     * Rejected validation.
     *
     * @param list<string|ErrorCode>|string|ErrorCode $errors
     * @param list<string|ErrorCode>|string|ErrorCode $warnings
     */
    public static function invalid(
        string|ErrorCode|array $errors,
        string|ErrorCode|array $warnings = [],
    ): self {
        [$errMsgs, $errCodes]   = self::resolve($errors);
        [$warnMsgs, $warnCodes] = self::resolve($warnings);

        return new self(
            valid: false,
            errors: $errMsgs,
            errorCodes: $errCodes,
            warnings: $warnMsgs,
            warningCodes: $warnCodes,
        );
    }

    /**
     * @return array{
     *     valid: bool,
     *     errors: list<string>,
     *     error_codes: list<string>,
     *     detail: ?ValidationDetail,
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
            'detail'      => $this->detail,
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
            return $this->warnings === [] ? 'valid' : implode('; ', $this->warnings);
        }

        return $this->errors === [] ? 'invalid' : implode('; ', $this->errors);
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
