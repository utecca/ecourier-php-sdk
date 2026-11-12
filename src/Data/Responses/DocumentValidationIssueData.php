<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

readonly class DocumentValidationIssueData
{
    public function __construct(
        public string $message,
        public ?string $code,
        public ?string $location,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            message: $data['message'],
            code: $data['code'] ?? null,
            location: $data['location'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'location' => $this->location,
        ];
    }
}
