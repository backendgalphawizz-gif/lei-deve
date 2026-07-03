<?php

namespace App\Http\Controllers\Applicant;

use App\Models\LeiApplication;
use Illuminate\Support\Facades\Storage;

class ApplicationTrackingController extends ApplicantPortalController
{
    public function index()
    {
        $this->sharePortalContext();
        $applications = $this->applications->applicationsForUser(auth()->user());

        $stats = [
            'pending' => $applications->whereIn('status', ['new', 'pending'])->count(),
            'in_review' => $applications->where('status', 'under_review')->count(),
            'approved' => $applications->where('status', 'approved')->count(),
            'clarification' => $applications->where('status', 'clarification')->count(),
        ];

        return view('applicant.applications.index', compact('applications', 'stats'));
    }

    public function show(LeiApplication $application)
    {
        $this->sharePortalContext();
        abort_unless($application->user_id === auth()->id(), 404);

        $application->load(['auditEvents', 'subscription', 'certificate']);

        $events = $application->auditEvents;

        return view('applicant.applications.show', compact('application', 'events'));
    }

    public function clarify(LeiApplication $application)
    {
        $this->sharePortalContext();
        abort_unless($application->user_id === auth()->id(), 404);

        return view('applicant.applications.clarify', compact('application'));
    }

    public function submitClarification(LeiApplication $application)
    {
        abort_unless($application->user_id === auth()->id(), 404);

        request()->validate([
            'response' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        return redirect()
            ->route('applicant.applications.show', $application)
            ->with('success', 'Your clarification response has been submitted.');
    }

    public function certificate(LeiApplication $application)
    {
        abort_unless($application->user_id === auth()->id(), 404);
        abort_unless($application->status === 'approved', 403);

        $certificate = $application->certificate;
        abort_unless($certificate?->isSigned() && $certificate->signed_pdf_path, 403, 'Your signed certificate is not yet available. It will be issued after CA digital signing.');
        abort_unless(Storage::disk('local')->exists($certificate->signed_pdf_path), 404);

        return Storage::disk('local')->download(
            $certificate->signed_pdf_path,
            'LEI-Certificate-'.$application->lei_number.'.pdf',
        );
    }
}
