<?php

declare(strict_types=1);

namespace Ecourier\Data;

class DocumentValidationData
{
    /**
     * @param DocumentValidationIssueData[] $errors
     * @param DocumentValidationIssueData[] $warnings
     */
    public function __construct(
        public readonly array $errors = [],
        public readonly array $warnings = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            errors: array_map(fn(array $issue) => DocumentValidationIssueData::fromArray($issue), $data['errors'] ?? []),
            warnings: array_map(fn(array $issue) => DocumentValidationIssueData::fromArray($issue), $data['warnings'] ?? []),
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

    public function toArray(): array
    {
        return [
            'errors' => array_map(fn(DocumentValidationIssueData $issue) => $issue->toArray(), $this->errors),
            'warnings' => array_map(fn(DocumentValidationIssueData $issue) => $issue->toArray(), $this->warnings),
        ];
    }
}
