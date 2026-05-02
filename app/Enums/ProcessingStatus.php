<?php

namespace App\Enums;

enum ProcessingStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case NeedConfirmation = 'need_confirmation';
    case Confirmed = 'confirmed';
    case Failed = 'failed';
}
