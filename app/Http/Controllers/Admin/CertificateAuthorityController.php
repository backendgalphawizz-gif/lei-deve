<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CertificateSignedMail;
use App\Models\LeiApplicationAuditEvent;
use App\Models\LeiCertificate;
use App\Services\LeiCertificateService;
use App\Services\CaDashboardStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateAuthorityController extends Controller
{
    public function __construct(private LeiCertificateService $certificates) {}

    public function index(Request $request, CaDashboardStatsService $caStats)
    {
        $status = $request->string('status')->toString() ?: 'pending_ca';

        $query = LeiCertificate::query()
            ->with(['application.user', 'application.subscription'])
            ->orderByDesc('updated_at');

        if ($status === 'pending_ca') {
            $query->whereIn('status', ['unsigned', 'pending_ca']);
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        $certificates = $query->paginate(15)->withQueryString();
        $stats = $caStats->summary();
        $recentSigned = $caStats->recentSigned(5);
        $signingTrend = $caStats->signingTrend(6);

        return view('admin.certificates.index', compact(
            'certificates',
            'stats',
            'status',
            'recentSigned',
            'signingTrend',
        ));
    }

    public function show(LeiCertificate $certificate)
    {
        $certificate->load([
            'application.user',
            'application.subscription.pricingPlan',
            'signer',
        ]);

        return view('admin.certificates.show', compact('certificate'));
    }

    public function sign(Request $request, LeiCertificate $certificate)
    {
        abort_unless(in_array($certificate->status, ['unsigned', 'pending_ca'], true), 422, 'Certificate is not awaiting signature.');

        $validated = $request->validate([
            'ca_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $certificate = $this->certificates->signByCa(
            $certificate,
            auth()->user(),
            $validated['ca_notes'] ?? null,
        );

        $application = $certificate->application;

        LeiApplicationAuditEvent::create([
            'lei_application_id' => $application->id,
            'occurred_at' => now(),
            'description' => 'LEI certificate digitally signed by CA: '.auth()->user()->name,
            'actor' => auth()->user()->name,
            'is_highlight' => true,
            'sort_order' => 0,
        ]);

        if ($application->user?->email) {
            try {
                Mail::to($application->user->email)->send(new CertificateSignedMail($application, $certificate));
            } catch (\Throwable) {
                // Non-fatal
            }
        }

        return redirect()
            ->route('admin.certificates.show', $certificate)
            ->with('success', 'Certificate digitally signed. The applicant can now download the signed certificate.');
    }

    public function uploadSignature(Request $request)
    {
        $validated = $request->validate([
            'signature' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $user = auth()->user();
        abort_unless($user->isCertificateAuthority(), 403);

        if ($user->ca_signature_path) {
            Storage::disk('local')->delete($user->ca_signature_path);
        }

        $ext = $request->file('signature')->getClientOriginalExtension() ?: 'png';
        $path = $request->file('signature')->storeAs(
            'ca-signatures',
            'user-'.$user->id.'-'.Str::random(6).'.'.strtolower($ext),
            'local',
        );

        $user->update(['ca_signature_path' => $path]);

        return back()->with('success', 'Digital signature image uploaded. It will appear on certificates you sign.');
    }

    public function removeSignature()
    {
        $user = auth()->user();
        abort_unless($user->isCertificateAuthority(), 403);

        if ($user->ca_signature_path) {
            Storage::disk('local')->delete($user->ca_signature_path);
            $user->update(['ca_signature_path' => null]);
        }

        return back()->with('success', 'Digital signature image removed.');
    }

    public function downloadUnsigned(LeiCertificate $certificate)
    {
        abort_unless($certificate->unsigned_pdf_path, 404);
        abort_unless(Storage::disk('local')->exists($certificate->unsigned_pdf_path), 404);

        return Storage::disk('local')->download(
            $certificate->unsigned_pdf_path,
            'LEI-Unsigned-'.$certificate->application->lei_number.'.pdf',
        );
    }

    public function downloadSigned(LeiCertificate $certificate)
    {
        abort_unless($certificate->signed_pdf_path, 404);
        abort_unless(Storage::disk('local')->exists($certificate->signed_pdf_path), 404);

        return Storage::disk('local')->download(
            $certificate->signed_pdf_path,
            'LEI-Certificate-'.$certificate->application->lei_number.'.pdf',
        );
    }
}
