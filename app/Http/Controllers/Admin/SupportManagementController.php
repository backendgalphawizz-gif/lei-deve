<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiSupportCategory;
use App\Models\LeiSupportMessage;
use App\Models\LeiSupportNote;
use App\Models\LeiSupportTicket;
use App\Services\SupportManagementService;
use Illuminate\Http\Request;

class SupportManagementController extends Controller
{
    public function __construct(private SupportManagementService $support) {}

    public function index(Request $request)
    {
        $perPage = 10;
        $query = $this->filteredQuery($request);

        $paginator = $query->paginate($perPage)->withQueryString();
        $tickets = $paginator->getCollection();

        $selectedId = $request->query('ticket');
        $selected = null;
        if ($selectedId) {
            $selected = LeiSupportTicket::with(['messages', 'notes'])->find($selectedId);
        }
        if (! $selected && $tickets->isNotEmpty()) {
            $selected = LeiSupportTicket::with(['messages', 'notes'])->find($tickets->first()->id);
        }

        $this->support->syncCategoryCounts();

        return view('admin.support.index', [
            'statCards' => $this->support->computeStatCards(),
            'tickets' => $tickets,
            'paginator' => $paginator,
            'selected' => $selected,
            'categories' => LeiSupportCategory::orderBy('sort_order')->get(),
            'filters' => $this->filterState($request),
            'adminInitials' => $this->support->adminInitials(),
            'grandTotal' => LeiSupportTicket::count(),
            'lastActivity' => $selected ? $this->support->lastActivityLabel($selected) : '',
        ]);
    }

    public function ticketDetail(LeiSupportTicket $ticket)
    {
        $ticket->load(['messages', 'notes']);

        return response()->json([
            'ok' => true,
            'html' => view('admin.support.partials.detail', [
                'selected' => $ticket,
                'adminInitials' => $this->support->adminInitials(),
                'lastActivity' => $this->support->lastActivityLabel($ticket),
            ])->render(),
            'row' => $this->support->ticketRowPayload($ticket),
            'stats' => $this->support->statsPayload(),
            'categories' => $this->support->categoriesPayload(),
            'urls' => [
                'message' => route('admin.support.message', $ticket),
                'note' => route('admin.support.note', $ticket),
                'action' => route('admin.support.action', $ticket),
            ],
        ]);
    }

    public function storeTicket(Request $request)
    {
        $data = $request->validate([
            'user_entity' => 'required|string|max:128',
            'category' => 'required|string|max:32',
            'priority_tone' => 'required|in:high,medium,low',
            'title' => 'required|string|max:255',
        ]);

        $maxSort = (int) LeiSupportTicket::max('sort_order');

        $ticket = LeiSupportTicket::create([
            'ticket_code' => $this->support->nextTicketCode(),
            'user_entity' => $data['user_entity'],
            'contact_email' => $this->support->emailFromEntity($data['user_entity']),
            'category' => $data['category'],
            'priority' => match ($data['priority_tone']) {
                'high' => 'High',
                'medium' => 'Med',
                default => 'Low',
            },
            'priority_tone' => $data['priority_tone'],
            'status' => 'Open',
            'status_tone' => 'open',
            'title' => $data['title'],
            'is_urgent' => $data['priority_tone'] === 'high',
            'sort_order' => $maxSort + 1,
        ]);

        $this->support->syncCategoryCounts();

        return response()->json([
            'ok' => true,
            'message' => 'Ticket ' . $ticket->ticket_code . ' created.',
            'redirect' => route('admin.support.index', ['ticket' => $ticket->id]),
            'stats' => $this->support->statsPayload(),
            'categories' => $this->support->categoriesPayload(),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:64|unique:lei_support_categories,name']);

        $cat = LeiSupportCategory::create([
            'name' => $data['name'],
            'ticket_count_label' => '0 Tickets',
            'sort_order' => (int) LeiSupportCategory::max('sort_order') + 1,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Category "' . $cat->name . '" added.',
            'categories' => $this->support->categoriesPayload(),
        ]);
    }

    public function updateCategory(Request $request, LeiSupportCategory $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:64|unique:lei_support_categories,name,' . $category->id,
        ]);

        $oldName = $category->name;
        $category->update(['name' => $data['name']]);
        LeiSupportTicket::where('category', $oldName)->update(['category' => $data['name']]);
        $this->support->syncCategoryCounts();

        return response()->json([
            'ok' => true,
            'message' => 'Category updated.',
            'categories' => $this->support->categoriesPayload(),
        ]);
    }

    public function sendMessage(Request $request, LeiSupportTicket $ticket)
    {
        $data = $request->validate(['body' => 'required|string|max:2000']);
        $initials = $this->support->adminInitials();

        $msg = LeiSupportMessage::create([
            'lei_support_ticket_id' => $ticket->id,
            'sender_initials' => $initials,
            'sender_name' => $this->support->adminDisplayName(),
            'sender_role' => 'Level 2 Support',
            'sender_tone' => 'support',
            'body' => $data['body'],
            'time_label' => now()->format('g:i A'),
            'is_outgoing' => true,
            'sort_order' => $ticket->messages()->count() + 1,
        ]);

        if ($ticket->status === 'Open') {
            $ticket->update(['status' => 'In Progress', 'status_tone' => 'progress']);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Message sent.',
            'msg' => [
                'initials' => $msg->sender_initials,
                'name' => $msg->sender_name,
                'role' => $msg->sender_role,
                'tone' => $msg->sender_tone,
                'body' => $msg->body,
                'time_label' => $msg->time_label,
                'is_outgoing' => true,
            ],
            'row' => $this->support->ticketRowPayload($ticket->fresh()),
            'stats' => $this->support->statsPayload(),
        ]);
    }

    public function addNote(Request $request, LeiSupportTicket $ticket)
    {
        $data = $request->validate(['body' => 'required|string|max:2000']);
        $initials = $this->support->adminInitials();

        $note = LeiSupportNote::create([
            'lei_support_ticket_id' => $ticket->id,
            'author_initials' => $initials,
            'author_name' => $this->support->adminDisplayName(),
            'author_tone' => 'admin',
            'body' => $data['body'],
            'time_label' => 'Just now',
            'sort_order' => $ticket->notes()->count() + 1,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Note saved.',
            'note' => [
                'initials' => $note->author_initials,
                'author_name' => $note->author_name,
                'tone' => $note->author_tone,
                'body' => $note->body,
                'time_label' => $note->time_label,
            ],
        ]);
    }

    public function ticketAction(Request $request, LeiSupportTicket $ticket)
    {
        $data = $request->validate(['action' => 'required|in:assign,escalate,close,priority']);

        match ($data['action']) {
            'assign' => $ticket->update([
                'status' => 'In Progress',
                'status_tone' => 'progress',
                'assigned_at' => now(),
            ]),
            'escalate' => $ticket->update([
                'status' => 'Escalated',
                'status_tone' => 'escalated',
                'is_urgent' => true,
            ]),
            'close' => $ticket->update([
                'status' => 'Closed',
                'status_tone' => 'closed',
                'is_urgent' => false,
                'closed_at' => now(),
            ]),
            'priority' => $ticket->update($this->support->cyclePriority($ticket)),
        };

        $ticket->refresh();
        $this->support->syncCategoryCounts();

        return response()->json([
            'ok' => true,
            'message' => 'Ticket updated.',
            'row' => $this->support->ticketRowPayload($ticket),
            'stats' => $this->support->statsPayload(),
            'html' => view('admin.support.partials.detail', [
                'selected' => $ticket->load(['messages', 'notes']),
                'adminInitials' => $this->support->adminInitials(),
                'lastActivity' => $this->support->lastActivityLabel($ticket),
            ])->render(),
            'urls' => [
                'message' => route('admin.support.message', $ticket),
                'note' => route('admin.support.note', $ticket),
                'action' => route('admin.support.action', $ticket),
            ],
        ]);
    }

    private function filteredQuery(Request $request)
    {
        $query = LeiSupportTicket::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $status = $request->query('status', 'active');
        if ($status === 'active') {
            $query->where('status', '!=', 'Closed');
        } elseif ($status !== 'all') {
            $query->where('status_tone', $status);
        }

        if ($request->filled('priority') && $request->query('priority') !== 'all') {
            $query->where('priority_tone', $request->query('priority'));
        }

        if ($request->filled('category') && $request->query('category') !== 'all') {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('q')) {
            $q = '%' . $request->query('q') . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('ticket_code', 'like', $q)
                    ->orWhere('user_entity', 'like', $q)
                    ->orWhere('contact_email', 'like', $q)
                    ->orWhere('title', 'like', $q);
            });
        }

        return $query;
    }

    private function filterState(Request $request): array
    {
        return [
            'status' => $request->query('status', 'active'),
            'priority' => $request->query('priority', 'all'),
            'category' => $request->query('category', 'all'),
            'q' => $request->query('q', ''),
        ];
    }

}
