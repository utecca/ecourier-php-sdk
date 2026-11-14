<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use Ecourier\Enums\Channel;
use Ecourier\Enums\Mode;

readonly class ParticipantLookupData
{
    public function __construct(
        public Channel $channel,
        public Mode $mode,
        public string $entityName,
        public ?string $country,
        public string $registrationDate,
        public string $orgNo,
        public string $registryUrl,
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
