<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use DateTimeImmutable;
use Ecourier\Data\Responses\Concerns\ParsesResponseData;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Direction;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\DocumentType;
use Ecourier\Enums\Mode;
use Ecourier\Enums\SubmissionFormat;

final readonly class DocumentData
{
    use ParsesResponseData;

    /**
     * @param string $webUrl Link to the document in the eCourier portal.
     * @param DocumentAttemptData[] $attempts
     */
    public function __construct(
        public string $id,
        public ?DocumentStatus $status,
        public Channel $channel,
        public Mode $mode,
        public Direction $direction,
        public ?DocumentType $type,
        public ?SubmissionFormat $submissionFormat,
        public DocumentParticipantData $sender,
        public DocumentParticipantData $recipient,
        public string $webUrl,
        public DateTimeImmutable $createdAt,
        public DocumentUblData $ubl,
        public DocumentValidationData $validation,
        public array $attempts,
        public ?string $latestE2eMessageUuid,
        public ?string $latestE2eTransmissionId,
        public ?DocumentCompanyData $company,
        public ?DateTimeImmutable $transmittedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: self::required($data, 'id'),
            status: self::enum(DocumentStatus::class, self::optional($data, 'status')),
            channel: self::requiredEnum($data, 'channel', Channel::class),
            mode: self::requiredEnum($data, 'mode', Mode::class),
            direction: self::requiredEnum($data, 'direction', Direction::class),
            type: self::enum(DocumentType::class, self::optional($data, 'type')),
            submissionFormat: self::enum(SubmissionFormat::class, self::optional($data, 'submission_format')),
            sender: DocumentParticipantData::fromArray(self::required($data, 'sender')),
            recipient: DocumentParticipantData::fromArray(self::required($data, 'recipient')),
            webUrl: self::required($data, 'web_url'),
            createdAt: self::date($data, 'created_at'),
            ubl: DocumentUblData::fromArray(self::required($data, 'ubl')),
            validation: DocumentValidationData::fromArray(self::required($data, 'validation')),
            attempts: array_map(fn(array $attempt) => DocumentAttemptData::fromArray($attempt), self::list($data, 'attempts')),
            latestE2eMessageUuid: self::optional($data, 'latest_e2e_message_uuid'),
            latestE2eTransmissionId: self::optional($data, 'latest_e2e_transmission_id'),
            company: isset($data['company']) ? DocumentCompanyData::fromArray($data['company']) : null,
            transmittedAt: self::optionalDate($data, 'transmitted_at'),
        );
    }
}
