<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Throwable;

class IndonesianDateNormalizer
{
    private const MONTHS = [
        'januari' => '01',
        'jan' => '01',
        'februari' => '02',
        'feb' => '02',
        'maret' => '03',
        'mar' => '03',
        'april' => '04',
        'apr' => '04',
        'mei' => '05',
        'may' => '05',
        'juni' => '06',
        'jun' => '06',
        'juli' => '07',
        'jul' => '07',
        'agustus' => '08',
        'agu' => '08',
        'aug' => '08',
        'september' => '09',
        'sep' => '09',
        'oktober' => '10',
        'okt' => '10',
        'oct' => '10',
        'november' => '11',
        'nov' => '11',
        'desember' => '12',
        'des' => '12',
        'dec' => '12',
    ];

    public function normalize(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $value = trim($value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            return $value;
        }

        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})$/', $value, $matches) === 1) {
            $year = (int) $matches[3];
            $year = $year < 100 ? 2000 + $year : $year;

            return sprintf('%04d-%02d-%02d', $year, (int) $matches[2], (int) $matches[1]);
        }

        if (preg_match('/^(\d{1,2})[\s\-]+([A-Za-z]+)[\s\-]+(\d{2,4})$/u', $value, $matches) === 1) {
            $month = self::MONTHS[strtolower($matches[2])] ?? null;

            if ($month) {
                $year = (int) $matches[3];
                $year = $year < 100 ? 2000 + $year : $year;

                return sprintf('%04d-%s-%02d', $year, $month, (int) $matches[1]);
            }
        }

        try {
            return CarbonImmutable::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
