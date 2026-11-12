<?php

declare(strict_types=1);

namespace Ecourier\Data\Requests;

use Ecourier\Data\Responses\CompanyAuthorisationSignerData;

readonly class SubmitAuthorisationPdfData
{
    /**
     * @param string $pdf Raw PDF file contents, not a file path.
     */
    public function __construct(
        public string $pdf,
        public CompanyAuthorisationSignerData $signer,
    ) {}
}
