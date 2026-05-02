<?php

namespace App\Http\Controllers;

use App\Enums\ValidityStatus;
use App\Models\Branch;
use App\Models\DocumentType;
use App\Models\Vessel;
use App\Models\VesselDocument;
use App\Support\DocumentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class MonitoringController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $branches = $this->availableBranches($request);
        $branchId = $this->selectedBranchId($request, $branches);
        $vessels = $branchId
            ? Vessel::where('branch_id', $branchId)->orderBy('name')->get(['id', 'branch_id', 'code', 'name'])
            : collect();
        $selectedVessel = $this->selectedVessel($request, $branchId, $vessels);
        $rows = $selectedVessel ? $this->monitoringRows($selectedVessel) : collect();

        return Inertia::render('MonitoringKapal', [
            'branches' => $branches->map(fn (Branch $branch): array => $this->branchPayload($branch))->values(),
            'vessels' => $vessels->map(fn (Vessel $vessel): array => $this->vesselPayload($vessel))->values(),
            'selectedBranchId' => $branchId,
            'selectedVesselId' => $selectedVessel?->id,
            'selectedBranch' => $branches->firstWhere('id', $branchId) ? $this->branchPayload($branches->firstWhere('id', $branchId)) : null,
            'selectedVessel' => $selectedVessel ? $this->vesselPayload($selectedVessel) : null,
            'branchLocked' => $user->isUserCabang(),
            'summary' => $this->summary($rows),
            'rows' => $rows->values(),
        ]);
    }

    private function availableBranches(Request $request): Collection
    {
        $user = $request->user();

        if ($user->isUserCabang()) {
            return Branch::whereKey($user->branch_id)->get();
        }

        return Branch::query()->orderBy('name')->get(['id', 'code', 'name']);
    }

    private function selectedBranchId(Request $request, Collection $branches): ?int
    {
        $user = $request->user();
        $requestedBranchId = $request->integer('branch_id') ?: null;

        if ($user->isUserCabang()) {
            if ($requestedBranchId && (int) $user->branch_id !== $requestedBranchId) {
                abort(403);
            }

            return (int) $user->branch_id;
        }

        if ($requestedBranchId) {
            abort_unless($branches->contains('id', $requestedBranchId), 404);

            return $requestedBranchId;
        }

        return $branches->first()?->id;
    }

    private function selectedVessel(Request $request, ?int $branchId, Collection $vessels): ?Vessel
    {
        if (! $branchId || $vessels->isEmpty()) {
            return null;
        }

        $requestedVesselId = $request->integer('vessel_id') ?: null;

        if (! $requestedVesselId) {
            return $vessels->first();
        }

        abort_unless($vessels->contains('id', $requestedVesselId), 404);

        return Vessel::whereKey($requestedVesselId)
            ->where('branch_id', $branchId)
            ->firstOrFail();
    }

    private function monitoringRows(Vessel $vessel): Collection
    {
        $documentsByType = VesselDocument::query()
            ->with(['documentType', 'uploadedBy'])
            ->where('vessel_id', $vessel->id)
            ->whereNotNull('document_type_id')
            ->latest('id')
            ->get()
            ->unique('document_type_id')
            ->keyBy('document_type_id');

        return DocumentType::query()
            ->where('required', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->values()
            ->map(function (DocumentType $documentType, int $index) use ($documentsByType, $vessel): array {
                /** @var VesselDocument|null $document */
                $document = $documentsByType->get($documentType->id);
                $status = DocumentStatus::for($document);

                return [
                    'no' => $index + 1,
                    'vessel_id' => $vessel->id,
                    'document_type' => [
                        'id' => $documentType->id,
                        'code' => $documentType->code,
                        'name' => $documentType->name,
                    ],
                    'document' => $document ? [
                        'id' => $document->id,
                        'letter_number' => $document->letter_number,
                        'issued_at' => $document->issued_at?->format('Y-m-d'),
                        'expires_at' => $document->expires_at?->format('Y-m-d'),
                        'issuer' => $document->issuer,
                        'is_permanent' => $document->is_permanent,
                        'status' => $status,
                        'processing_status' => $document->processing_status,
                        'has_file' => filled($document->file_path),
                        'external_link' => $document->external_link,
                        'preview_url' => filled($document->file_path) ? route('documents.preview', $document) : null,
                        'download_url' => filled($document->file_path) ? route('documents.download', $document) : null,
                    ] : null,
                    'status' => $status,
                    'upload_url' => route('targeted-uploads.create', [
                        'vessel' => $vessel->id,
                        'documentType' => $documentType->id,
                    ]),
                ];
            });
    }

    private function summary(Collection $rows): array
    {
        $total = $rows->count();
        $completed = $rows->whereNotNull('document')->count();
        $counts = $rows->countBy('status');

        return [
            'totalRequiredDocuments' => $total,
            'documentsWithRecords' => $completed,
            'missingDocuments' => $counts->get(ValidityStatus::Missing->value, 0),
            'expiredDocuments' => $counts->get(ValidityStatus::Expired->value, 0),
            'expiringSoonDocuments' => $counts->get(ValidityStatus::ExpiringSoon->value, 0),
            'permanentDocuments' => $counts->get(ValidityStatus::Permanent->value, 0),
            'completionPercentage' => $total === 0 ? 0 : round(($completed / $total) * 100),
        ];
    }

    private function branchPayload(Branch $branch): array
    {
        return [
            'id' => $branch->id,
            'code' => $branch->code,
            'name' => $branch->name,
        ];
    }

    private function vesselPayload(Vessel $vessel): array
    {
        return [
            'id' => $vessel->id,
            'branch_id' => $vessel->branch_id,
            'code' => $vessel->code,
            'name' => $vessel->name,
        ];
    }
}
