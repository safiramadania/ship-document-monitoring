<?php

namespace App\Http\Controllers;

use App\Enums\ProcessingStatus;
use App\Enums\UploadMode;
use App\Enums\ValidityStatus;
use App\Jobs\ProcessVesselDocumentJob;
use App\Models\Branch;
use App\Models\Vessel;
use App\Models\VesselDocument;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SmartUploadController extends Controller
{
    public function __construct(private readonly AuditService $auditService) {}

    public function index(Request $request): Response
    {
        $branches = $this->availableBranches($request);
        $branchId = $this->selectedBranchId($request, $branches);
        $vessels = $branchId
            ? Vessel::query()
                ->where('branch_id', $branchId)
                ->orderBy('name')
                ->get(['id', 'branch_id', 'code', 'name'])
            : collect();

        return Inertia::render('SmartUpload', [
            'branches' => $branches->map(fn (Branch $branch): array => $this->branchPayload($branch))->values(),
            'vessels' => $vessels->map(fn (Vessel $vessel): array => $this->vesselPayload($vessel))->values(),
            'selectedBranchId' => $branchId,
            'selectedBranch' => $branches->firstWhere('id', $branchId) ? $this->branchPayload($branches->firstWhere('id', $branchId)) : null,
            'branchLocked' => $request->user()->isUserCabang(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vessel_id' => ['required', 'exists:vessels,id'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
        ]);

        $vessel = Vessel::query()
            ->with('branch:id,code,name')
            ->findOrFail($validated['vessel_id']);

        abort_unless($request->user()->canAccessBranch($vessel->branch_id), 403);

        $file = $validated['document'];
        $extension = $file->getClientOriginalExtension() ?: $file->extension();
        $path = $file->storeAs(
            "vessel-documents/{$vessel->id}",
            Str::uuid().'.'.$extension,
            'local'
        );

        $vesselDocument = VesselDocument::create([
            'vessel_id' => $vessel->id,
            'document_type_id' => null,
            'uploaded_by' => $request->user()->id,
            'upload_mode' => UploadMode::Smart->value,
            'processing_status' => ProcessingStatus::Pending->value,
            'validity_status' => ValidityStatus::Unknown->value,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        $this->auditService->log(
            action: 'document.uploaded',
            entity: $vesselDocument,
            newValues: [
                'vessel_id' => $vessel->id,
                'upload_mode' => UploadMode::Smart->value,
                'processing_status' => ProcessingStatus::Pending->value,
            ],
            request: $request,
        );

        ProcessVesselDocumentJob::dispatchSync($vesselDocument->id);

        return redirect()
            ->route('ocr.confirmation', $vesselDocument)
            ->with('success', 'Dokumen berhasil diunggah. Sistem sudah mencoba mengklasifikasikan jenis dokumen.');
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
