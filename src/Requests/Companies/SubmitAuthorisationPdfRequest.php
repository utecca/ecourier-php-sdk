<?php

declare(strict_types=1);

namespace Ecourier\Requests\Companies;

use Ecourier\Data\CompanyAuthorisationSignerData;
use Ecourier\Data\CompanyData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasMultipartBody;

class SubmitAuthorisationPdfRequest extends Request implements HasBody
{
    use HasMultipartBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $company,
        private readonly string $pdf,
        private readonly CompanyAuthorisationSignerData $signer,
        private readonly string $filename = 'authorisation.pdf',
    ) {}

    public function resolveEndpoint(): string
    {
        return "/companies/{$this->company}/authorisation/pdf";
    }

    protected function defaultBody(): array
    {
        return [
            new MultipartValue('pdf', $this->pdf, $this->filename, ['Content-Type' => 'application/pdf']),
            new MultipartValue('signer[first_name]', $this->signer->firstName),
            new MultipartValue('signer[last_name]', $this->signer->lastName),
            new MultipartValue('signer[title]', $this->signer->title),
        ];
    }

    public function createDtoFromResponse(Response $response): CompanyData
    {
        return CompanyData::fromArray($response->array());
    }
}
