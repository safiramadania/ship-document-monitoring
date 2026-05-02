<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserApprovalController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('UserApproval', [
            'pendingUsers' => User::query()
                ->with('branch:id,code,name')
                ->where('status', UserStatus::Pending->value)
                ->latest()
                ->get()
                ->map(fn (User $user): array => $this->userRow($user))
                ->values(),
            'branches' => Branch::query()
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
        ]);
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->status === UserStatus::Pending->value, 404);

        $validated = $request->validate([
            'role' => ['required', Rule::in([UserRole::Admin->value, UserRole::UserCabang->value])],
            'branch_id' => [
                Rule::requiredIf($request->input('role') === UserRole::UserCabang->value),
                'nullable',
                'exists:branches,id',
            ],
        ]);

        $user->forceFill([
            'status' => UserStatus::Active->value,
            'role' => $validated['role'],
            'branch_id' => $validated['role'] === UserRole::UserCabang->value
                ? $validated['branch_id']
                : null,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejected_reason' => null,
        ])->save();

        return back()->with('success', 'User berhasil disetujui.');
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->status === UserStatus::Pending->value, 404);

        $validated = $request->validate([
            'rejected_reason' => ['required', 'string', 'max:1000'],
        ]);

        $user->forceFill([
            'status' => UserStatus::Rejected->value,
            'rejected_reason' => $validated['rejected_reason'],
            'approved_by' => null,
            'approved_at' => null,
        ])->save();

        return back()->with('success', 'User berhasil ditolak.');
    }

    private function userRow(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'job_title' => $user->job_title,
            'created_at' => $user->created_at?->toDateTimeString(),
            'branch' => $user->branch ? [
                'id' => $user->branch->id,
                'code' => $user->branch->code,
                'name' => $user->branch->name,
            ] : null,
        ];
    }
}
