<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Users', [
            'users' => User::query()
                ->with('branch:id,code,name')
                ->latest()
                ->get()
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'job_title' => $user->job_title,
                    'last_login_at' => $user->last_login_at?->toDateTimeString(),
                    'last_seen_at' => $user->last_seen_at?->toDateTimeString(),
                    'last_seen_label' => $this->lastSeenLabel($user),
                    'online_recently' => $user->last_seen_at?->gte(now()->subMinutes(5)) ?? false,
                    'created_at' => $user->created_at?->toDateTimeString(),
                    'branch' => $user->branch ? [
                        'id' => $user->branch->id,
                        'code' => $user->branch->code,
                        'name' => $user->branch->name,
                    ] : null,
                ])
                ->values(),
        ]);
    }

    private function lastSeenLabel(User $user): string
    {
        if (! $user->last_seen_at) {
            return 'Never seen';
        }

        if ($user->last_seen_at->gte(now()->subMinutes(5))) {
            return 'Online recently';
        }

        return 'Last seen '.$user->last_seen_at->diffForHumans();
    }
}
