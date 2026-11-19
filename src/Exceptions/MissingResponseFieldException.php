<?php

declare(strict_types=1);

namespace Ecourier\Exceptions;

use UnexpectedValueException;

/**
 * Thrown when an API response is missing a field the SDK requires to build a data object.
 *
 * It is not an {@see EcourierException}: the request itself succeeded, but the response
 * didn't have the shape this version of the SDK expects.
 */
class MissingResponseFieldException extends UnexpectedValueException
{
    /**
     * @param class-string $dataClass The data object that was being built.
     * @param string $field The missing field, as named in the API response.
     */
    public function __construct(
        public readonly string $dataClass,
        public readonly string $field,
    ) {
        parent::__construct("The eCourier response is missing the required field \"{$field}\" for {$dataClass}.");
    }
}
