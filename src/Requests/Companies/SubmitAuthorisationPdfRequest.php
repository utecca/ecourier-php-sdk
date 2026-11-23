<?php

declare(strict_types=1);

namespace Ecourier\Requests\Companies;

use Ecourier\Data\Requests\SubmitAuthorisationPdfData;
use Ecourier\Data\Responses\CompanyData;
use Ecourier\Requests\Concerns\EncodesPathParameters;
use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasMultipartBody;

class SubmitAuthorisationPdfRequest extends Request implements HasBody
{
    use EncodesPathParameters;
    use HasMultipartBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $company,
        private readonly SubmitAuthorisationPdfData $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/companies/' . self::pathParameter($this->company, 'company ID') . '/authorisation/pdf';
    }

    protected function defaultBody(): array
    {
        return [
            new MultipartValue('pdf', $this->data->pdf, 'authorisation.pdf', ['Content-Type' => 'application/pdf']),
            new MultipartValue('signer[first_name]', $this->data->signer->firstName),
            new MultipartValue('signer[last_name]', $this->data->signer->lastName),
            new MultipartValue('signer[title]', $this->data->signer->title),
        ];
    }

    public function createDtoFromResponse(Response $response): CompanyData
    {
        return CompanyData::fromArray($response->array());
    }
}
