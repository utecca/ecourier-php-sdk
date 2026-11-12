<?php

declare(strict_types=1);

namespace Ecourier\Enums;

/**
 * The languages supported by eCourier.
 */
enum Locale: string
{
    case DA = 'da'; // Danish
    case DE = 'de'; // German
    case EN = 'en'; // English
    case NB = 'nb'; // Norwegian Bokmål
    case SV = 'sv'; // Swedish
}
