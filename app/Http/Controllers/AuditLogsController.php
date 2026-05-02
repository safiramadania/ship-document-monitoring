<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\VesselDocument;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogsController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'action' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'entity_type' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $logs = AuditLog::query()
            ->with(['user.branch:id,code,name'])
            ->when($filters['action'] ?? null, fn ($query, $action) => $query->where('action', $action))
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filters['entity_type'] ?? null, fn ($query, $entityType) => $query->where('entity_type', $entityType))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest('created_at')
            ->limit(100)
            ->get();

        return Inertia::render('AuditLogs', [
            'logs' => $logs->map(fn (AuditLog $log): array => $this->logPayload($log))->values(),
            'filters' => [
                'action' => $filters['action'] ?? '',
                'user_id' => $filters['user_id'] ?? '',
                'entity_type' => $filters['entity_type'] ?? '',
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
            ],
            'actions' => AuditLog::query()
                ->select('action')
                ->distinct()
                ->orderBy('action')
                ->pluck('action'),
            'entityTypes' => AuditLog::query()
                ->whereNotNull('entity_type')
                ->select('entity_type')
                ->distinct()
                ->orderBy('entity_type')
                ->pluck('entity_type')
                ->map(fn (string $entityType): array => [
                    'value' => $entityType,
                    'label' => class_basename($entityType),
                ]),
            'users' => User::query()
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]),
        ]);
    }

    private function logPayload(AuditLog $log): array
    {
        return [
            'id' => $log->id,
            'timestamp' => $log->created_at?->toDateTimeString(),
            'user' => $log->user ? [
                'id' => $log->user->id,
                'name' => $log->user->name,
                'email' => $log->user->email,
                'role' => $log->user->role,
                'branch' => $log->user->branch ? [
                    'id' => $log->user->branch->id,
                    'code' => $log->user->branch->code,
                    'name' => $log->user->branch->name,
                ] : null,
            ] : null,
            'action' => $log->action,
            'entity_type' => $log->entity_type,
            'entity_label' => $log->entity_type ? class_basename($log->entity_type) : '-',
            'entity_id' => $log->entity_id,
            'summary' => $this->summary($log),
            'old_values' => $log->old_values,
            'new_values' => $log->new_values,
            'change_summary' => $this->changeSummary($log->old_values, $log->new_values),
        ];
    }

    private function summary(AuditLog $log): string
    {
        if ($log->entity_type === VesselDocument::class && $log->entity_id) {
            $document = VesselDocument::query()
                ->with(['vessel.branch', 'documentType'])
                ->find($log->entity_id);

            if ($document) {
                return implode(' - ', array_filter([
                    $document->vessel?->branch?->name,
                    $document->vessel?->name,
                    $document->documentType?->name ?? 'Dokumen belum diklasifikasi',
                ]));
            }
        }

        if ($log->entity_type === User::class && $log->entity_id) {
            $user = User::find($log->entity_id);

            return $user ? "{$user->name} ({$user->email})" : 'User record';
        }

        return trim(($log->entity_type ? class_basename($log->entity_type) : 'System').' #'.($log->entity_id ?? '-'));
    }

    private function changeSummary(?array $oldValues, ?array $newValues): string
    {
        if (! $oldValues && ! $newValues) {
            return '-';
        }

        $fields = array_slice(array_unique(array_merge(
            array_keys($oldValues ?: []),
            array_keys($newValues ?: [])
        )), 0, 4);

        return collect($fields)
            ->map(function (string $field) use ($oldValues, $newValues): string {
                $old = $this->formatValue($oldValues[$field] ?? null);
                $new = $this->formatValue($newValues[$field] ?? null);

                if (! $oldValues) {
                    return "{$field}: {$new}";
                }

                return "{$field}: {$old} -> {$new}";
            })
            ->implode('; ');
    }

    private function formatValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value) ?: 'array';
        }

        return blank($value) ? '-' : (string) $value;
    }
}
