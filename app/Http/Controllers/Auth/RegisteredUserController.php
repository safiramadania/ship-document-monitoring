<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'branches' => Branch::query()
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'branch_id' => ['required', 'exists:branches,id'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => UserRole::UserCabang->value,
            'status' => UserStatus::Pending->value,
            'branch_id' => $request->branch_id,
            'job_title' => $request->job_title,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        $this->auditService->log(
            action: 'user.registered',
            entity: $user,
            newValues: [
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'branch_id' => $user->branch_id,
                'job_title' => $user->job_title,
            ],
            request: $request,
        );

        Auth::login($user);

        return redirect(route('approval.pending', absolute: false));
    }
}
