<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiWorkflowState;
use App\Models\LeiWorkflowTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TemplateManagementController extends Controller
{
    public function index(Request $request)
    {
        $template = LeiWorkflowTemplate::where('is_active', true)->with('states')->first();

        if (! $template) {
            return view('admin.templates.index', [
                'template' => null,
                'states' => collect(),
                'modules' => $this->modules(),
            ]);
        }

        return view('admin.templates.index', [
            'template' => $template,
            'states' => $template->states,
            'modules' => $this->modules(),
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'module' => ['required', 'string', 'max:64'],
            'sla_hours' => ['required', 'integer', 'min:1', 'max:720'],
        ]);

        $template = LeiWorkflowTemplate::where('is_active', true)->first();
        if (! $template) {
            return response()->json(['ok' => false, 'message' => 'No active template. Run TemplateManagementSeeder.'], 422);
        }

        $nodeCount = $template->states()->where('rule_type', '!=', 'final_placeholder')->count();
        $template->update([
            'name' => $validated['name'],
            'module' => $validated['module'],
            'sla_hours' => $validated['sla_hours'],
            'total_nodes_label' => $nodeCount.' '.($nodeCount === 1 ? 'State' : 'States'),
            'last_synced_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Workflow template saved and synchronized with registry.',
            'synced_at' => $template->fresh()->last_synced_at?->format('H:i'),
        ]);
    }

    public function storeState(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:200'],
            'rule_label' => ['nullable', 'string', 'max:64'],
            'accent' => ['required', 'in:core,auto,approval'],
        ]);

        $template = LeiWorkflowTemplate::where('is_active', true)->firstOrFail();
        $maxOrder = $template->states()->max('sort_order') ?? 0;

        $state = $template->states()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'rule_label' => $validated['rule_label'] ?? 'TRANSITION RULE: MANUAL',
            'accent' => $validated['accent'],
            'rule_type' => 'transition',
            'sort_order' => $maxOrder + 1,
        ]);

        $count = $template->states()->where('rule_type', '!=', 'final_placeholder')->count();
        $template->update([
            'total_nodes_label' => $count.' '.($count === 1 ? 'State' : 'States'),
            'last_synced_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'State added to workflow.',
            'state' => [
                'id' => $state->id,
                'rule_label' => $state->rule_label,
                'title' => $state->title,
                'description' => $state->description,
                'accent' => $state->accent,
            ],
        ]);
    }

    /** @return array<string, string> */
    private function modules(): array
    {
        return [
            'registry_services' => 'Registry Services',
            'payments' => 'Payments',
            'master_data' => 'Master Data',
            'environment' => 'Environment Management',
        ];
    }
}
