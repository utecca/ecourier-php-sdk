<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use DateTimeImmutable;
use Ecourier\Data\Responses\Concerns\ParsesResponseData;
use Ecourier\Enums\DocumentAttemptStatus;

final readonly class DocumentAttemptData
{
    use ParsesResponseData;

    public function __construct(
        public ?DocumentAttemptStatus $status,
        public ?string $errorMessage,
        public string $e2eMessageUuid,
        public ?string $e2eTransmissionId,
        public DateTimeImmutable $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: self::enum(DocumentAttemptStatus::class, self::optional($data, 'status')),
            errorMessage: self::optional($data, 'error_message'),
            e2eMessageUuid: self::required($data, 'e2e_message_uuid'),
            e2eTransmissionId: self::optional($data, 'e2e_transmission_id'),
            createdAt: self::date($data, 'created_at'),
        );
    }
}
