<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GlobalSearchService;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __construct(private GlobalSearchService $search) {}

    public function index(Request $request)
    {
        $q = $request->string('q')->trim()->toString();
        $groups = $q !== '' ? $this->search->search($q) : [];
        $total = $q !== '' ? $this->search->totalCount($q) : 0;

        return view('admin.search.index', [
            'query' => $q,
            'groups' => array_values(array_filter($groups, fn ($g) => $g['count'] > 0)),
            'total' => $total,
        ]);
    }

    public function suggest(Request $request)
    {
        $q = $request->string('q')->trim()->toString();
        if (strlen($q) < 2) {
            return response()->json(['ok' => true, 'items' => []]);
        }

        return response()->json([
            'ok' => true,
            'items' => $this->search->suggest($q),
            'more_url' => route('admin.search', ['q' => $q]),
        ]);
    }
}
