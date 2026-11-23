<?php

declare(strict_types=1);

namespace Ecourier\Exceptions;

use Throwable;

/**
 * Thrown when a field in an API response has a type or format the SDK cannot read,
 * e.g. a number where a string is expected.
 */
class InvalidResponseFieldException extends UnexpectedResponseException
{
    /**
     * @param class-string $dataClass The data object that was being built.
     * @param string $field The invalid field, as named in the API response.
     * @param string $expected What the field must be, e.g. "a string".
     */
    public function __construct(
        public readonly string $dataClass,
        public readonly string $field,
        string $expected,
        mixed $value,
        ?Throwable $previous = null,
    ) {
        $given = get_debug_type($value);

        parent::__construct(
            "The eCourier response field \"{$field}\" for {$dataClass} must be {$expected}, {$given} given.",
            previous: $previous,
        );
    }
}
