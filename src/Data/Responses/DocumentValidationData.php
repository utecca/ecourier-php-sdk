<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use Ecourier\Data\Responses\Concerns\ParsesResponseData;

final readonly class DocumentValidationData
{
    use ParsesResponseData;

    /**
     * @param DocumentValidationIssueData[] $errors
     * @param DocumentValidationIssueData[] $warnings
     */
    public function __construct(
        public array $errors,
        public array $warnings,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            errors: array_map(fn(array $issue) => DocumentValidationIssueData::fromArray($issue), self::list($data, 'errors')),
            warnings: array_map(fn(array $issue) => DocumentValidationIssueData::fromArray($issue), self::list($data, 'warnings')),
        );
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    public function hasWarnings(): bool
    {
        return $this->warnings !== [];
    }
}
