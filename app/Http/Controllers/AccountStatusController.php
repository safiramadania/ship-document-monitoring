<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class AccountStatusController extends Controller
{
    public function pending(): Response
    {
        return Inertia::render('Auth/PendingApproval');
    }

    public function rejected(): Response
    {
        return Inertia::render('Auth/RejectedAccount', [
            'rejectedReason' => auth()->user()?->rejected_reason,
        ]);
    }
}
