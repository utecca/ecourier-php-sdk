<?php

declare(strict_types=1);

namespace Ecourier\Exceptions;

use UnexpectedValueException;

/**
 * Thrown when the API answers successfully, but the response doesn't have the shape
 * this version of the SDK expects.
 *
 * It is not an {@see EcourierException}: the request itself succeeded.
 */
class UnexpectedResponseException extends UnexpectedValueException {}
