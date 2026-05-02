<?php

namespace Database\Seeders;

use App\Enums\ProcessingStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Branch;
use App\Models\DocumentType;
use App\Models\User;
use App\Models\Vessel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBranches();
        $this->seedVessels();
        $this->seedDocumentTypes();
        $this->seedUsers();
        $this->seedExistingDocuments();
    }

    private function seedBranches(): void
    {
        foreach ($this->csvRows('branches.csv') as $row) {
            Branch::updateOrCreate(
                ['code' => $row['branch_code']],
                [
                    'name' => $row['branch_name'],
                    'regional' => $this->nullable($row['regional']),
                    'email' => $this->nullable($row['email_list']),
                    'source_link' => $this->nullable($row['source_excel_link']),
                ]
            );
        }
    }

    private function seedVessels(): void
    {
        $branches = Branch::query()->pluck('id', 'code');

        foreach ($this->csvRows('vessels.csv') as $row) {
            $branchId = $branches[$row['branch_code']] ?? null;

            if (! $branchId) {
                continue;
            }

            Vessel::updateOrCreate(
                [
                    'branch_id' => $branchId,
                    'code' => $this->nullable($row['vessel_code']),
                ],
                [
                    'name' => $row['vessel_name'],
                    'operator' => $this->nullable($row['operator']),
                    'status' => $this->nullable($row['status']) ?? 'active',
                ]
            );
        }
    }

    private function seedDocumentTypes(): void
    {
        foreach ($this->csvRows('document_types.csv') as $row) {
            DocumentType::updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'agency' => $this->nullable($row['agency']),
                    'category' => $this->nullable($row['category']),
                    'required' => $this->boolean($row['required'], true),
                    'permanent_allowed' => $this->boolean($row['permanent_allowed']),
                    'validity_months' => $this->integerOrNull($row['validity_months']),
                    'sort_order' => $this->integerOrNull($row['sort_order']),
                    'aliases' => $this->pipeList($row['aliases']),
                    'keywords' => $this->pipeList($row['keywords']),
                ]
            );
        }
    }

    private function seedUsers(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@asdp.local'],
            [
                'name' => 'ASDP Super Admin',
                'password' => Hash::make('password'),
                'role' => UserRole::SuperAdmin->value,
                'status' => UserStatus::Active->value,
                'branch_id' => null,
                'job_title' => 'System Administrator',
                'email_verified_at' => now(),
                'approved_at' => now(),
            ]
        );

        $branches = Branch::query()->pluck('id', 'code');

        foreach ($this->csvRows('branch_users.csv') as $row) {
            $email = Str::lower(trim($row['email']));

            if ($email === '') {
                continue;
            }

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => Str::title(Str::before($email, '@')),
                    'password' => Hash::make('password'),
                    'role' => UserRole::UserCabang->value,
                    'status' => $this->validUserStatus($row['status']),
                    'branch_id' => $branches[$row['branch_code']] ?? null,
                    'job_title' => $this->nullable($row['job_title']),
                    'email_verified_at' => now(),
                ]
            );
        }
    }

    private function seedExistingDocuments(): void
    {
        DB::table('vessel_documents')
            ->whereNull('uploaded_by')
            ->whereNull('file_path')
            ->delete();

        $vessels = Vessel::query()->pluck('id', 'code');
        $documentTypes = DocumentType::query()->pluck('id', 'code');
        $now = now();
        $records = [];

        foreach ($this->csvRows('existing_documents.csv') as $row) {
            $vesselId = $vessels[$row['vessel_code']] ?? null;
            $documentTypeId = $documentTypes[$row['document_type_code']] ?? null;

            if (! $vesselId || ! $documentTypeId) {
                continue;
            }

            $isPermanent = $this->boolean($row['is_permanent']) || Str::lower($row['expires_at']) === 'permanent';

            $records[] = [
                'vessel_id' => $vesselId,
                'document_type_id' => $documentTypeId,
                'uploaded_by' => null,
                'confirmed_by' => null,
                'confirmed_at' => $now,
                'upload_mode' => null,
                'letter_number' => $this->nullable($row['letter_number']),
                'issued_at' => $this->dateOrNull($row['issued_at']),
                'expires_at' => $isPermanent ? null : $this->dateOrNull($row['expires_at']),
                'issuer' => null,
                'is_permanent' => $isPermanent,
                'validity_status' => $this->nullable($row['validity_status']) ?? 'unknown',
                'processing_status' => $this->nullable($row['processing_status']) ?? ProcessingStatus::Confirmed->value,
                'file_path' => null,
                'original_filename' => null,
                'mime_type' => null,
                'file_size' => null,
                'ocr_text' => null,
                'classification_confidence' => null,
                'extraction_confidence' => null,
                'extracted_values' => null,
                'final_values' => json_encode([
                    'letter_number' => $this->nullable($row['letter_number']),
                    'issued_at' => $this->dateOrNull($row['issued_at']),
                    'expires_at' => $isPermanent ? null : $this->dateOrNull($row['expires_at']),
                    'is_permanent' => $isPermanent,
                ]),
                'warnings' => null,
                'processing_error' => null,
                'external_link' => $this->nullable($row['source_link']),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($records) === 500) {
                DB::table('vessel_documents')->insert($records);
                $records = [];
            }
        }

        if ($records !== []) {
            DB::table('vessel_documents')->insert($records);
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function csvRows(string $filename): array
    {
        $path = database_path("seed-data/{$filename}");
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [];
        }

        $headers = fgetcsv($handle) ?: [];
        $headers = array_map(fn (string $header): string => trim($this->stripBom($header)), $headers);
        $rows = [];

        while (($data = fgetcsv($handle)) !== false) {
            $row = [];

            foreach ($headers as $index => $header) {
                $row[$header] = isset($data[$index]) ? trim((string) $data[$index]) : '';
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function stripBom(string $value): string
    {
        return preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
    }

    private function nullable(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function boolean(?string $value, bool $default = false): bool
    {
        if ($value === null || trim($value) === '') {
            return $default;
        }

        return in_array(Str::lower(trim($value)), ['1', 'true', 'yes', 'y', 'aktif'], true);
    }

    private function integerOrNull(?string $value): ?int
    {
        $value = trim((string) $value);

        return $value === '' ? null : (int) $value;
    }

    /**
     * @return array<int, string>|null
     */
    private function pipeList(?string $value): ?array
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return array_values(array_filter(array_map('trim', explode('|', $value))));
    }

    private function dateOrNull(?string $value): ?string
    {
        $value = trim((string) $value);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return $value;
    }

    private function validUserStatus(?string $value): string
    {
        $status = Str::lower(trim((string) $value));

        return in_array($status, array_column(UserStatus::cases(), 'value'), true)
            ? $status
            : UserStatus::Pending->value;
    }
}
