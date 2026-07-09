<?php

declare(strict_types=1);

namespace Ecourier\Data\Invoice;

class InvoicePartyData
{
    public function __construct(
        public readonly ParticipantIdentifier $participant,
        public readonly ?SimplifiedAddressData $address = null,
        public readonly ?InvoiceContactData $contact = null,
    ) {}

    public function toArray(): array
    {
        $data = ['participant' => $this->participant->toArray()];

        if ($this->address !== null) {
            $data['simplified_address'] = $this->address->toArray();
        }

        if ($this->contact !== null) {
            $data['contact'] = $this->contact->toArray();
        }

        return $data;
    }
}
