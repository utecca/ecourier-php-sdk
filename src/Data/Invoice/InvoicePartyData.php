<?php

declare(strict_types=1);

namespace Ecourier\Data\Invoice;

class InvoicePartyData
{
    public function __construct(
        public readonly ParticipantIdentifier $participant,
        public readonly ?string $name = null,
        public readonly ?string $registrationNumber = null,
        public readonly ?string $vatId = null,
        public readonly ?SimplifiedAddressData $simplifiedAddress = null,
        public readonly ?InvoiceContactData $contact = null,
    ) {}

    public function toArray(): array
    {
        $data = ['participant' => $this->participant->toArray()];

        foreach ([
            'name' => $this->name,
            'registration_number' => $this->registrationNumber,
            'vat_id' => $this->vatId,
        ] as $key => $value) {
            if ($value !== null) {
                $data[$key] = $value;
            }
        }

        if ($this->simplifiedAddress !== null) {
            $data['simplified_address'] = $this->simplifiedAddress->toArray();
        }

        if ($this->contact !== null) {
            $data['contact'] = $this->contact->toArray();
        }

        return $data;
    }
}
