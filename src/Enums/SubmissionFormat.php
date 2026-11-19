<?php

declare(strict_types=1);

namespace Ecourier\Enums;

enum SubmissionFormat: string
{
    case XML = 'XML';
    case JSON = 'JSON';
}
