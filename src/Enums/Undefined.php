<?php

declare(strict_types=1);

namespace Ecourier\Enums;

/**
 * A sentinel used to distinguish an omitted argument from one explicitly set to null.
 */
enum Undefined
{
    case value;
}
