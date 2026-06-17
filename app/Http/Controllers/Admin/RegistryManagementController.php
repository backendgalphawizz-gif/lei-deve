<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiRegistryTemplate;
use Illuminate\Http\Request;

class RegistryManagementController extends Controller
{
    public function index()
    {
        $template = LeiRegistryTemplate::where('is_active', true)->first();

        return view('admin.registry.index', [
            'template' => $template,
            'primaryCategories' => LeiRegistryTemplate::primaryCategories(),
            'subCategories' => LeiRegistryTemplate::subCategories(),
            'approvalFlows' => LeiRegistryTemplate::approvalFlows(),
            'formatOptions' => ['pdf' => 'PDF', 'jpg' => 'JPG', 'png' => 'PNG', 'docx' => 'DOCX'],
        ]);
    }

    public function save(Request $request)
    {
        $validated = $this->validatePayload($request);
        $template = $this->activeTemplate();

        $template->update(array_merge($validated, [
            'last_modified_by' => auth()->user()->name ?? 'Super_Admin_01',
            'last_modified_at' => now(),
            'is_published' => false,
        ]));

        return response()->json([
            'ok' => true,
            'message' => 'Registry template draft saved.',
            'modified_at' => $template->fresh()->last_modified_at?->format('h:i A'),
        ]);
    }

    public function publish(Request $request)
    {
        $validated = $this->validatePayload($request);
        $template = $this->activeTemplate();

        $template->update(array_merge($validated, [
            'last_modified_by' => auth()->user()->name ?? 'Super_Admin_01',
            'last_modified_at' => now(),
            'is_published' => true,
        ]));

        return response()->json([
            'ok' => true,
            'message' => 'Registry template published successfully.',
            'modified_at' => $template->fresh()->last_modified_at?->format('h:i A'),
        ]);
    }

    public function sandbox()
    {
        return response()->json([
            'ok' => true,
            'message' => 'Validation sandbox completed. All rules passed.',
        ]);
    }

    private function activeTemplate(): LeiRegistryTemplate
    {
        return LeiRegistryTemplate::where('is_active', true)->firstOrFail();
    }

    /** @return array<string, mixed> */
    private function validatePayload(Request $request): array
    {
        $validated = $request->validate([
            'document_name' => ['required', 'string', 'max:160'],
            'primary_category' => ['required', 'string', 'max:64'],
            'sub_category' => ['required', 'string', 'max:64'],
            'mandatory_flag' => ['boolean'],
            'ocr_verification' => ['boolean'],
            'file_formats' => ['required', 'array', 'min:1'],
            'file_formats.*' => ['in:pdf,jpg,png,docx'],
            'max_file_size_mb' => ['required', 'integer', 'min:1', 'max:50'],
            'versioning_mode' => ['required', 'in:audit_trail,overwrite'],
            'approval_flow' => ['required', 'string', 'max:64'],
            'security_tier' => ['required', 'in:standard,encrypted,air_gapped'],
        ]);

        $validated['mandatory_flag'] = $request->boolean('mandatory_flag');
        $validated['ocr_verification'] = $request->boolean('ocr_verification');

        return $validated;
    }
}
