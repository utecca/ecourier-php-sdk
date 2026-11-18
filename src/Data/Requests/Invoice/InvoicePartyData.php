<?php

declare(strict_types=1);

namespace Ecourier\Data\Requests\Invoice;

readonly class InvoicePartyData
{
    public function __construct(
        public ParticipantIdentifier $participant,
        public string $name,
        public SimplifiedAddressData $simplifiedAddress,
        public ?string $registrationNumber = null,
        public ?string $vatId = null,
        public ?InvoiceContactData $contact = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'participant' => $this->participant->toArray(),
            'name' => $this->name,
            'simplified_address' => $this->simplifiedAddress->toArray(),
        ];

        if ($this->registrationNumber !== null) {
            $data['registration_number'] = $this->registrationNumber;
        }

        if ($this->vatId !== null) {
            $data['vat_id'] = $this->vatId;
        }

        if ($this->contact !== null) {
            $data['contact'] = $this->contact->toArray();
        }

        return $data;
    }
}
