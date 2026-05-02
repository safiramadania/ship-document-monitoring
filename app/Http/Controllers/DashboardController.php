<?php

namespace App\Http\Controllers;

use App\Enums\ProcessingStatus;
use App\Enums\UserStatus;
use App\Enums\ValidityStatus;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\DocumentType;
use App\Models\User;
use App\Models\Vessel;
use App\Models\VesselDocument;
use App\Support\DocumentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'dashboardData' => match (true) {
                $user->isSuperAdmin() => $this->superAdminData(),
                $user->isAdmin() => $this->adminData(),
                default => $this->branchData((int) $user->branch_id),
            },
        ]);
    }

    public function cabang(Request $request): Response
    {
        return Inertia::render('DashboardCabang', [
            'dashboardData' => $this->branchData((int) $request->user()->branch_id),
        ]);
    }

    private function superAdminData(): array
    {
        return [
            'stats' => [
                'totalBranches' => Branch::count(),
                'totalVessels' => Vessel::count(),
                'totalUsers' => User::count(),
                'pendingUsers' => User::where('status', UserStatus::Pending->value)->count(),
                'activeUsers' => User::where('status', UserStatus::Active->value)->count(),
                'totalDocumentTypes' => DocumentType::count(),
                'totalVesselDocuments' => VesselDocument::count(),
            ],
            'recentUsers' => User::query()
                ->with('branch:id,code,name')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'branch' => $this->branchPayload($user->branch),
                    'created_at' => $user->created_at?->toDateTimeString(),
                ]),
            'recentApprovals' => User::query()
                ->with(['branch:id,code,name', 'approvedBy:id,name'])
                ->whereIn('status', [UserStatus::Active->value, UserStatus::Rejected->value])
                ->where(function (Builder $query): void {
                    $query->whereNotNull('approved_at')
                        ->orWhereNotNull('rejected_reason');
                })
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'approved_by' => $user->approvedBy?->name,
                    'approved_at' => $user->approved_at?->toDateTimeString(),
                    'updated_at' => $user->updated_at?->toDateTimeString(),
                ]),
            'recentSystemActivity' => $this->recentAuditActivity(),
        ];
    }

    private function adminData(): array
    {
        $documents = VesselDocument::query()
            ->with(['vessel.branch', 'documentType', 'uploadedBy'])
            ->get();

        return [
            'stats' => [
                'totalBranches' => Branch::count(),
                'totalVessels' => Vessel::count(),
                'totalVesselDocuments' => $documents->count(),
                ...$this->statusCounts($documents),
                'missingDocuments' => $this->missingCount(Vessel::query()),
                'documentsNeedConfirmation' => VesselDocument::where('processing_status', ProcessingStatus::NeedConfirmation->value)->count(),
            ],
            'recentUploads' => $this->recentUploads(VesselDocument::query()),
            'recentDocumentEdits' => $this->recentDocumentEdits(),
        ];
    }

    private function branchData(int $branchId): array
    {
        $branch = Branch::find($branchId);
        $vesselIds = Vessel::where('branch_id', $branchId)->pluck('id');
        $documents = VesselDocument::query()
            ->with(['vessel.branch', 'documentType', 'uploadedBy'])
            ->whereIn('vessel_id', $vesselIds)
            ->get();

        return [
            'branch' => $this->branchPayload($branch),
            'stats' => [
                'totalVessels' => $vesselIds->count(),
                'totalVesselDocuments' => $documents->count(),
                ...$this->statusCounts($documents),
                'missingDocuments' => $this->missingCount(Vessel::where('branch_id', $branchId)),
                'documentsNeedConfirmation' => VesselDocument::whereIn('vessel_id', $vesselIds)
                    ->where('processing_status', ProcessingStatus::NeedConfirmation->value)
                    ->count(),
            ],
            'recentUploads' => $this->recentUploads(VesselDocument::query()->whereIn('vessel_id', $vesselIds)),
        ];
    }

    private function statusCounts(Collection $documents): array
    {
        $counts = $documents
            ->map(fn (VesselDocument $document): string => DocumentStatus::for($document))
            ->countBy();

        return [
            'activeDocuments' => $counts->get(ValidityStatus::Active->value, 0),
            'expiringSoonDocuments' => $counts->get(ValidityStatus::ExpiringSoon->value, 0),
            'expiredDocuments' => $counts->get(ValidityStatus::Expired->value, 0),
            'permanentDocuments' => $counts->get(ValidityStatus::Permanent->value, 0),
            'unknownDocuments' => $counts->get(ValidityStatus::Unknown->value, 0),
        ];
    }

    private function missingCount(Builder $vesselsQuery): int
    {
        $vesselIds = (clone $vesselsQuery)->pluck('id');
        $requiredDocumentTypes = DocumentType::where('required', true)->count();

        if ($vesselIds->isEmpty() || $requiredDocumentTypes === 0) {
            return 0;
        }

        $documentPairs = VesselDocument::query()
            ->whereIn('vessel_id', $vesselIds)
            ->whereNotNull('document_type_id')
            ->select(['vessel_id', 'document_type_id'])
            ->distinct()
            ->get()
            ->count();

        return max(($vesselIds->count() * $requiredDocumentTypes) - $documentPairs, 0);
    }

    private function recentUploads(Builder $query): Collection
    {
        return $query
            ->with(['vessel.branch', 'documentType', 'uploadedBy'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (VesselDocument $document): array => [
                'id' => $document->id,
                'vessel' => $document->vessel?->name,
                'branch' => $document->vessel?->branch?->name,
                'document_type' => $document->documentType?->name,
                'uploader' => $document->uploadedBy?->name,
                'created_at' => $document->created_at?->toDateTimeString(),
                'status' => DocumentStatus::for($document),
            ]);
    }

    private function recentAuditActivity(): Collection
    {
        return AuditLog::query()
            ->with('user:id,name')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (AuditLog $log): array => [
                'id' => $log->id,
                'title' => $log->action,
                'description' => trim(($log->entity_type ?? 'System').' #'.($log->entity_id ?? '-')),
                'timestamp' => $log->created_at?->toDateTimeString(),
                'status' => 'unknown',
            ]);
    }

    private function recentDocumentEdits(): Collection
    {
        return AuditLog::query()
            ->with('user:id,name')
            ->whereIn('action', ['document.updated', 'document.confirmed'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (AuditLog $log): array => [
                'id' => $log->id,
                'timestamp' => $log->created_at?->toDateTimeString(),
                'user' => $log->user?->name,
                'action' => $log->action,
                'summary' => 'Document change recorded.',
            ]);
    }

    private function branchPayload(?Branch $branch): ?array
    {
        if (! $branch) {
            return null;
        }

        return [
            'id' => $branch->id,
            'code' => $branch->code,
            'name' => $branch->name,
        ];
    }
}
