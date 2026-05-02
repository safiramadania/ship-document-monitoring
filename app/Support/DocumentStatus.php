<?php

namespace App\Support;

use App\Enums\ValidityStatus;
use App\Models\VesselDocument;
use Carbon\CarbonInterface;

class DocumentStatus
{
    public static function for(?VesselDocument $document, ?CarbonInterface $today = null): string
    {
        if (! $document) {
            return ValidityStatus::Missing->value;
        }

        if ($document->validity_status) {
            return $document->validity_status;
        }

        if ($document->is_permanent) {
            return ValidityStatus::Permanent->value;
        }

        if (! $document->expires_at) {
            return ValidityStatus::Unknown->value;
        }

        $today ??= now();

        if ($document->expires_at->lt($today->copy()->startOfDay())) {
            return ValidityStatus::Expired->value;
        }

        if ($document->expires_at->lte($today->copy()->addDays(60)->endOfDay())) {
            return ValidityStatus::ExpiringSoon->value;
        }

        return ValidityStatus::Active->value;
    }
}
