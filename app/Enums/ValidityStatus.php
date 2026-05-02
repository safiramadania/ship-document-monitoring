<?php

namespace App\Enums;

enum ValidityStatus: string
{
    case Active = 'active';
    case ExpiringSoon = 'expiring_soon';
    case Expired = 'expired';
    case Permanent = 'permanent';
    case Missing = 'missing';
    case Unknown = 'unknown';
}
