<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Services\ApplicantApplicationService;
use Illuminate\Support\Facades\View;

abstract class ApplicantPortalController extends Controller
{
    public function __construct(protected ApplicantApplicationService $applications)
    {
        View::share('portalDraft', null);
    }

    protected function sharePortalContext(): void
    {
        $user = auth()->user();
        View::share('portalDraft', $this->applications->activeDraft($user));
    }
}
