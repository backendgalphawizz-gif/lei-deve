<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiContactSubmission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactEnquiryController extends Controller
{
    public function index(Request $request)
    {
        $query = LeiContactSubmission::query()->latest();

        if ($status = $request->string('status')->trim()->toString()) {
            if (array_key_exists($status, LeiContactSubmission::statuses())) {
                $query->where('status', $status);
            }
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $submissions = $query->paginate(15)->withQueryString();
        $selected = null;

        if ($id = $request->integer('id')) {
            $selected = LeiContactSubmission::find($id);
        } elseif ($submissions->isNotEmpty()) {
            $selected = $submissions->first();
        }

        return view('admin.contact-enquiries.index', [
            'submissions' => $submissions,
            'selected' => $selected,
            'stats' => [
                'total' => LeiContactSubmission::count(),
                'new' => LeiContactSubmission::where('status', 'new')->count(),
                'replied' => LeiContactSubmission::where('status', 'replied')->count(),
            ],
            'statuses' => LeiContactSubmission::statuses(),
        ]);
    }

    public function update(Request $request, LeiContactSubmission $submission)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(LeiContactSubmission::statuses()))],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($data['status'] !== 'new' && ! $submission->read_at) {
            $data['read_at'] = now();
        }

        $submission->update($data);

        return redirect()
            ->route('admin.contact-enquiries.index', ['id' => $submission->id])
            ->with('success', 'Enquiry updated.');
    }

    public function destroy(LeiContactSubmission $submission)
    {
        $submission->delete();

        return redirect()
            ->route('admin.contact-enquiries.index')
            ->with('success', 'Enquiry deleted.');
    }
}
