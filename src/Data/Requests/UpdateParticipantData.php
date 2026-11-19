<?php

declare(strict_types=1);

namespace Ecourier\Data\Requests;

use Ecourier\Enums\Channel;
use Ecourier\Enums\Undefined;

final readonly class UpdateParticipantData
{
    /**
     * @param ?Channel[] $channels
     * @param string|Undefined|null $capabilitySetId Omit to keep the current capability set, or pass
     *     `null` to reset to the default one.
     */
    public function __construct(
        public ?array $channels = null,
        public string|Undefined|null $capabilitySetId = Undefined::value,
    ) {}
}
