<?php

namespace App\Services;

use App\Models\LeiApplication;
use App\Models\LeiDocument;
use App\Models\LeiStaticPage;
use App\Models\LeiSupportTicket;
use App\Models\User;

class GlobalSearchService
{
    public function search(string $query, int $limitPerGroup = 8): array
    {
        $q = trim($query);
        if ($q === '') {
            return [];
        }

        return [
            $this->group('Users', 'users', $this->searchUsers($q, $limitPerGroup)),
            $this->group('Applications', 'applications', $this->searchApplications($q, $limitPerGroup)),
            $this->group('Support Tickets', 'support', $this->searchSupport($q, $limitPerGroup)),
            $this->group('Documents', 'documents', $this->searchDocuments($q, $limitPerGroup)),
            $this->group('Static Pages', 'pages', $this->searchStaticPages($q, $limitPerGroup)),
        ];
    }

    public function suggest(string $query, int $limit = 12): array
    {
        $flat = [];
        foreach ($this->search($query, 3) as $group) {
            foreach ($group['items'] as $item) {
                $flat[] = $item;
                if (count($flat) >= $limit) {
                    return $flat;
                }
            }
        }

        return $flat;
    }

    public function totalCount(string $query): int
    {
        $n = 0;
        foreach ($this->search($query, 100) as $group) {
            $n += count($group['items']);
        }

        return $n;
    }

    private function group(string $label, string $key, array $items): array
    {
        return [
            'label' => $label,
            'key' => $key,
            'items' => $items,
            'count' => count($items),
        ];
    }

    private function searchUsers(string $q, int $limit): array
    {
        return User::query()
            ->where('role', '!=', 'super_admin')
            ->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('system_id', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (User $u) => [
                'type' => 'user',
                'title' => $u->name,
                'meta' => $u->email,
                'url' => route('admin.users.index', ['q' => $u->email]),
            ])
            ->all();
    }

    private function searchApplications(string $q, int $limit): array
    {
        return LeiApplication::query()
            ->where(function ($w) use ($q) {
                $w->where('application_code', 'like', "%{$q}%")
                    ->orWhere('entity_name', 'like', "%{$q}%")
                    ->orWhere('country', 'like', "%{$q}%");
            })
            ->orderByDesc('submitted_on')
            ->limit($limit)
            ->get()
            ->map(fn (LeiApplication $a) => [
                'type' => 'application',
                'title' => $a->entity_name,
                'meta' => $a->application_code . ' · ' . strtoupper($a->status),
                'url' => route('admin.applications.show', $a),
            ])
            ->all();
    }

    private function searchSupport(string $q, int $limit): array
    {
        return LeiSupportTicket::query()
            ->where(function ($w) use ($q) {
                $w->where('ticket_code', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhere('user_entity', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (LeiSupportTicket $t) => [
                'type' => 'support',
                'title' => $t->title,
                'meta' => $t->ticket_code . ' · ' . $t->status,
                'url' => route('admin.support.index', ['ticket' => $t->id, 'q' => $q]),
            ])
            ->all();
    }

    private function searchDocuments(string $q, int $limit): array
    {
        return LeiDocument::query()
            ->where(function ($w) use ($q) {
                $w->where('document_code', 'like', "%{$q}%")
                    ->orWhere('file_name', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (LeiDocument $d) => [
                'type' => 'document',
                'title' => $d->file_name,
                'meta' => $d->document_code . ' · ' . ucfirst($d->status),
                'url' => route('admin.documents.index', ['doc' => $d->id, 'q' => $q]),
            ])
            ->all();
    }

    private function searchStaticPages(string $q, int $limit): array
    {
        return LeiStaticPage::query()
            ->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            })
            ->orderBy('sort_order')
            ->limit($limit)
            ->get()
            ->map(fn (LeiStaticPage $p) => [
                'type' => 'page',
                'title' => $p->title,
                'meta' => '/' . $p->slug,
                'url' => route('admin.static-pages.edit', $p),
            ])
            ->all();
    }
}
