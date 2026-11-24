<?php

declare(strict_types=1);

namespace Ecourier\Requests\Concerns;

use InvalidArgumentException;

/**
 * Puts a caller-supplied value into a request path as exactly one path segment, so that it cannot
 * change which endpoint is called or add a query string.
 *
 * @internal
 */
trait EncodesPathParameters
{
    /**
     * @param string $name What the value is, for the exception message, e.g. "document ID".
     *
     * @throws InvalidArgumentException If the value is empty, or is "." or "..".
     */
    protected static function pathParameter(string $value, string $name): string
    {
        if ($value === '') {
            throw new InvalidArgumentException("The {$name} must not be empty.");
        }

        // Dots are not percent-encoded, and HTTP clients resolve these two segments to another path.
        if ($value === '.' || $value === '..') {
            throw new InvalidArgumentException("The {$name} must not be \"{$value}\".");
        }

        return rawurlencode($value);
    }
}
