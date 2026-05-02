<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        return match ($user->status) {
            UserStatus::Active->value => $next($request),
            UserStatus::Pending->value => redirect()->route('approval.pending'),
            UserStatus::Rejected->value => redirect()->route('approval.rejected'),
            default => abort(403),
        };
    }
}
