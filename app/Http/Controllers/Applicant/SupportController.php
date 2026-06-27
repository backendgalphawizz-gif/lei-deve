<?php

namespace App\Http\Controllers\Applicant;

use App\Models\LeiSupportCategory;
use App\Models\LeiSupportMessage;
use App\Models\LeiSupportTicket;
use App\Services\ApplicantApplicationService;
use App\Services\SupportManagementService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportController extends ApplicantPortalController
{
    public function __construct(
        ApplicantApplicationService $applications,
        protected SupportManagementService $support,
    ) {
        parent::__construct($applications);
    }

    public function index()
    {
        $this->sharePortalContext();
        $user = auth()->user();

        $tickets = LeiSupportTicket::query()
            ->where('contact_email', $user->email)
            ->orderByDesc('updated_at')
            ->get();

        $base = LeiSupportTicket::query()->where('contact_email', $user->email);

        $stats = [
            'total' => (clone $base)->count(),
            'in_progress' => (clone $base)->where('status', 'In Progress')->count(),
            'awaiting' => (clone $base)->whereIn('status', ['Open', 'Escalated'])->count(),
            'resolved' => (clone $base)->where('status', 'Closed')->count(),
        ];

        return view('applicant.support.index', compact('tickets', 'stats'));
    }

    public function create()
    {
        $this->sharePortalContext();

        $categories = LeiSupportCategory::orderBy('sort_order')->pluck('name');

        if ($categories->isEmpty()) {
            $categories = collect(['Verification', 'Payment', 'Technical', 'General']);
        }

        return view('applicant.support.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $categories = LeiSupportCategory::orderBy('sort_order')->pluck('name')->all();
        if ($categories === []) {
            $categories = ['Verification', 'Payment', 'Technical', 'General'];
        }

        $data = $request->validate([
            'category' => ['required', 'string', 'max:32', Rule::in($categories)],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'subject' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        $user = auth()->user();
        $priority = $this->mapPriority($data['priority']);
        $maxSort = (int) LeiSupportTicket::max('sort_order');

        $ticket = LeiSupportTicket::create([
            'ticket_code' => $this->support->nextTicketCode(),
            'user_entity' => $this->userEntityLabel($user),
            'contact_email' => $user->email,
            'category' => $data['category'],
            'priority' => $priority['priority'],
            'priority_tone' => $priority['priority_tone'],
            'status' => 'Open',
            'status_tone' => 'open',
            'title' => $data['subject'],
            'is_urgent' => $priority['is_urgent'],
            'sort_order' => $maxSort + 1,
        ]);

        LeiSupportMessage::create([
            'lei_support_ticket_id' => $ticket->id,
            'sender_initials' => $this->userInitials($user->name),
            'sender_name' => $user->name,
            'sender_tone' => 'client',
            'body' => $data['description'],
            'time_label' => now()->format('g:i A'),
            'is_outgoing' => false,
            'sort_order' => 1,
        ]);

        $this->support->syncCategoryCounts();

        return redirect()
            ->route('applicant.support.show', $ticket)
            ->with('success', 'Support ticket ' . $ticket->ticket_code . ' submitted. Our team will respond within 4 business hours.');
    }

    public function show(LeiSupportTicket $ticket)
    {
        $this->sharePortalContext();
        $this->authorizeTicket($ticket);

        $ticket->load(['messages' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')]);

        $hasStaffReply = $ticket->messages->contains(fn ($msg) => $msg->is_outgoing);

        return view('applicant.support.show', compact('ticket', 'hasStaffReply'));
    }

    public function reply(Request $request, LeiSupportTicket $ticket)
    {
        $this->sharePortalContext();
        $this->authorizeTicket($ticket);

        if ($ticket->status === 'Closed') {
            return back()->with('error', 'This ticket is closed. Raise a new ticket if you need further help.');
        }

        $data = $request->validate([
            'reply_body' => ['required', 'string', 'max:5000'],
        ]);

        $user = auth()->user();
        $nextSort = (int) $ticket->messages()->max('sort_order') + 1;

        LeiSupportMessage::create([
            'lei_support_ticket_id' => $ticket->id,
            'sender_initials' => $this->userInitials($user->name),
            'sender_name' => $user->name,
            'sender_tone' => 'client',
            'body' => $data['reply_body'],
            'time_label' => now()->format('g:i A'),
            'is_outgoing' => false,
            'sort_order' => $nextSort,
        ]);

        $ticket->touch();

        return redirect()
            ->route('applicant.support.show', $ticket)
            ->with('success', 'Your reply has been sent to our support team.');
    }

    private function authorizeTicket(LeiSupportTicket $ticket): void
    {
        abort_unless($ticket->contact_email === auth()->user()->email, 403);
    }

    private function userEntityLabel($user): string
    {
        $org = $user->organization?->name;

        return $org ? $user->name . ' — ' . $org : $user->name;
    }

    private function userInitials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }

    /**
     * @return array{priority: string, priority_tone: string, is_urgent: bool}
     */
    private function mapPriority(string $priority): array
    {
        return match ($priority) {
            'high' => ['priority' => 'High', 'priority_tone' => 'high', 'is_urgent' => true],
            'urgent' => ['priority' => 'High', 'priority_tone' => 'high', 'is_urgent' => true],
            'medium' => ['priority' => 'Med', 'priority_tone' => 'medium', 'is_urgent' => false],
            default => ['priority' => 'Low', 'priority_tone' => 'low', 'is_urgent' => false],
        };
    }
}
