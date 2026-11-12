<?php

declare(strict_types=1);

namespace Ecourier\Enums;

enum DocumentAttemptStatus: string
{
    case Pending = 'Pending';
    case Success = 'Success';
    case ValidationError = 'ValidationError';
    case NetworkError = 'NetworkError';
    case RecipientNotFound = 'RecipientNotFound';
    case ProfileNotSupported = 'ProfileNotSupported';
    case SenderProfileNotSupported = 'SenderProfileNotSupported';
    case RecipientCertificateInvalid = 'RecipientCertificateInvalid';
    case UnknownError = 'UnknownError';
}
