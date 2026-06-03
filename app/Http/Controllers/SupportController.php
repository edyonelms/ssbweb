<?php

namespace App\Http\Controllers;

use App\Models\SupportQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();

        $baseQuery = SupportQuery::with(['user:id,name,mobile,avatar_path', 'replies.user:id,name,role']);

        if (! $isAdmin) {
            $baseQuery->where('user_id', $user->id);
        }

        $queries = (clone $baseQuery)->orderByDesc('id')->get();

        $statsScope = $isAdmin
            ? SupportQuery::query()
            : SupportQuery::where('user_id', $user->id);

        $stats = [
            'total'     => (clone $statsScope)->count(),
            'month'     => (clone $statsScope)->whereYear('created_at', now()->year)
                                              ->whereMonth('created_at', now()->month)
                                              ->count(),
            'pending'   => (clone $statsScope)->where('status', SupportQuery::STATUS_PENDING)->count(),
            'approved'  => (clone $statsScope)->where('status', SupportQuery::STATUS_APPROVED)->count(),
        ];

        return view('support.index', [
            'queries' => $queries,
            'stats'   => $stats,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject'     => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'file'        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:2048'],
        ], [
            'file.mimes' => 'File must be a PDF, image (JPG/PNG/WEBP) or Word document.',
            'file.max'   => 'File must be 2MB or smaller.',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path'] = $file->store('uploads/support', 'public');
            $data['file_original_name'] = $file->getClientOriginalName();
        }

        SupportQuery::create([
            'user_id'            => $request->user()->id,
            'subject'            => $data['subject'],
            'description'        => $data['description'] ?? null,
            'file_path'          => $data['file_path'] ?? null,
            'file_original_name' => $data['file_original_name'] ?? null,
            'status'             => SupportQuery::STATUS_PENDING,
        ]);

        return redirect()
            ->route('support.index')
            ->with('status', 'Your query has been submitted.');
    }

    public function reply(Request $request, SupportQuery $query): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required_without:file', 'nullable', 'string', 'max:5000'],
            'file'    => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:2048'],
        ], [
            'message.required_without' => 'Type a reply or attach a file.',
            'file.mimes' => 'File must be a PDF, image (JPG/PNG/WEBP) or Word document.',
            'file.max'   => 'File must be 2MB or smaller.',
        ]);

        $replyData = [
            'support_query_id'   => $query->id,
            'user_id'            => $request->user()->id,
            'message'            => $data['message'] ?? null,
            'file_path'          => null,
            'file_original_name' => null,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $replyData['file_path'] = $file->store('uploads/support', 'public');
            $replyData['file_original_name'] = $file->getClientOriginalName();
        }

        $query->replies()->create($replyData);

        // First admin reply marks the query approved/resolved.
        if ($query->isPending()) {
            $query->update([
                'status'      => SupportQuery::STATUS_APPROVED,
                'resolved_at' => now(),
            ]);
        }

        return redirect()
            ->route('support.index', ['view' => $query->id])
            ->with('status', 'Reply sent.');
    }
}
