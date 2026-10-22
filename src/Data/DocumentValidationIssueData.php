<?php

declare(strict_types=1);

namespace Ecourier\Data;

class DocumentValidationIssueData
{
    public function __construct(
        public readonly string $message,
        public readonly ?string $code = null,
        public readonly ?string $location = null,
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
