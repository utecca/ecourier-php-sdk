<?php

declare(strict_types=1);

namespace Ecourier\Data;

use Ecourier\Enums\Channel;
use Ecourier\Enums\Mode;

class ParticipantData
{
    public function __construct(
        public readonly Channel $channel,
        public readonly Mode $mode,
        public readonly string $entityName,
        public readonly string $country,
        public readonly string $registrationDate,
        public readonly string $orgNo,
        public readonly string $registryUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            channel: Channel::from($data['channel']),
            mode: Mode::from($data['mode']),
            entityName: $data['entityName'],
            country: $data['country'],
            registrationDate: $data['registrationDate'],
            orgNo: $data['orgNo'],
            registryUrl: $data['registryUrl'],
        );
    }

    public function toArray(): array
    {
        return [
            'channel' => $this->channel->value,
            'mode' => $this->mode->value,
            'entityName' => $this->entityName,
            'country' => $this->country,
            'registrationDate' => $this->registrationDate,
            'orgNo' => $this->orgNo,
            'registryUrl' => $this->registryUrl,
        ];
    }
}
