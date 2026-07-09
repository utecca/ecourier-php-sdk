<?php

declare(strict_types=1);

namespace Ecourier\Enums;

enum SubmissionFormat: string
{
    case XML = 'XML';
    case GOBL = 'GOBL';
    case JSON = 'JSON';
}
