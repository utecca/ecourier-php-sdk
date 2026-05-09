<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Enums;

enum AccountSchemeId: string
{
    case IBAN = 'IBAN';
    case DK_BBAN = 'DK:BBAN';
}
