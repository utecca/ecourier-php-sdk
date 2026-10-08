<?php

declare(strict_types=1);

namespace Ecourier\Enums;

enum Channel: string
{
    case Peppol = 'Peppol';
    case NemHandel = 'NemHandel';

    /**
     * The identifier schemes the network can route to. NemHandel only supports Danish identifiers and GLN,
     * while Peppol supports every scheme.
     *
     * @return list<IdentifierScheme>
     */
    public function supportedSchemes(): array
    {
        return match ($this) {
            self::NemHandel => [
                IdentifierScheme::DK_CVR,
                IdentifierScheme::DK_SE,
                IdentifierScheme::DK_P,
                IdentifierScheme::GLN,
            ],
            self::Peppol => IdentifierScheme::cases(),
        };
    }
}
