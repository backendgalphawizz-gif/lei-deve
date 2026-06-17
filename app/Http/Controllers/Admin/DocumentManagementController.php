<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiDocument;
use App\Models\LeiDocumentAuditEvent;
use App\Models\LeiDocumentConfig;
use App\Services\DocumentManagementService;
use Illuminate\Http\Request;

class DocumentManagementController extends Controller
{
    public function __construct(private DocumentManagementService $documents) {}

    public function index(Request $request)
    {
        $query = LeiDocument::query()->orderBy('sort_order');
        if ($request->filled('type') && $request->query('type') !== 'all') {
            $query->where('file_type', $request->query('type'));
        }
        if ($request->filled('q')) {
            $q = '%' . $request->query('q') . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('document_code', 'like', $q)
                    ->orWhere('file_name', 'like', $q);
            });
        }

        $docs = $query->get();
        $selectedId = $request->query('doc', $docs->firstWhere('status_tone', 'review')?->id ?? $docs->first()?->id);
        $selected = LeiDocument::with('auditEvents')->find($selectedId) ?? $docs->first();

        return view('admin.documents.index', [
            'statCards' => $this->documents->computeStatCards(),
            'config' => LeiDocumentConfig::first(),
            'documents' => $docs,
            'selected' => $selected?->load('auditEvents'),
            'filterType' => $request->query('type', 'all'),
        ]);
    }

    public function documentDetail(LeiDocument $document)
    {
        $document->load('auditEvents');

        return response()->json([
            'ok' => true,
            'html' => view('admin.documents.partials.side-panel', [
                'selected' => $document,
                'config' => LeiDocumentConfig::first(),
            ])->render(),
            'viewer' => view('admin.documents.partials.viewer', ['selected' => $document])->render(),
            'doc' => $this->documents->documentPayload($document),
            'stats' => $this->documents->statsPayload(),
            'urls' => [
                'verify' => route('admin.documents.verify', $document),
                'reject' => route('admin.documents.reject', $document),
            ],
        ]);
    }

    public function verify(Request $request, LeiDocument $document)
    {
        $data = $request->validate(['reason' => 'nullable|string|max:2000']);
        $document->update([
            'status' => 'Verified',
            'status_tone' => 'verified',
            'decision_reason' => $data['reason'] ?? null,
            'verified_at' => now(),
        ]);
        LeiDocumentAuditEvent::create([
            'lei_document_id' => $document->id,
            'title' => 'Document Verified',
            'description' => $data['reason'] ?? 'Approved by administrator.',
            'event_label' => now()->format('M d, Y - h:i A'),
            'indicator_tone' => 'green',
            'is_in_progress' => false,
            'sort_order' => $document->auditEvents()->count() + 1,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Document verified successfully.',
            'doc' => $this->documents->documentPayload($document->fresh()),
            'stats' => $this->documents->statsPayload(),
            'html' => view('admin.documents.partials.side-panel', [
                'selected' => $document->fresh()->load('auditEvents'),
                'config' => LeiDocumentConfig::first(),
            ])->render(),
            'viewer' => view('admin.documents.partials.viewer', ['selected' => $document->fresh()])->render(),
        ]);
    }

    public function reject(Request $request, LeiDocument $document)
    {
        $data = $request->validate(['reason' => 'required|string|max:2000']);
        $document->update([
            'status' => 'Rejected',
            'status_tone' => 'rejected',
            'decision_reason' => $data['reason'],
            'rejected_at' => now(),
        ]);
        LeiDocumentAuditEvent::create([
            'lei_document_id' => $document->id,
            'title' => 'Application Rejected',
            'description' => $data['reason'],
            'event_label' => now()->format('M d, Y - h:i A'),
            'indicator_tone' => 'red',
            'is_in_progress' => false,
            'sort_order' => $document->auditEvents()->count() + 1,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Application rejected.',
            'doc' => $this->documents->documentPayload($document->fresh()),
            'stats' => $this->documents->statsPayload(),
            'html' => view('admin.documents.partials.side-panel', [
                'selected' => $document->fresh()->load('auditEvents'),
                'config' => LeiDocumentConfig::first(),
            ])->render(),
            'viewer' => view('admin.documents.partials.viewer', ['selected' => $document->fresh()])->render(),
        ]);
    }
}
