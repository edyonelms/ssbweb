<?php

namespace App\Http\Controllers;

use App\Models\SupportQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    /** Allowed values for the ?period= chip in the UI. */
    private const PERIOD_OPTIONS = ['all', '7', '15', '30'];

    /** Allowed values for the ?status= chip in the UI. "replied" maps to STATUS_APPROVED. */
    private const STATUS_OPTIONS = ['all', 'pending', 'replied'];

    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();

        $period = in_array($request->query('period'), self::PERIOD_OPTIONS, true)
            ? $request->query('period')
            : 'all';

        $status = in_array($request->query('status'), self::STATUS_OPTIONS, true)
            ? $request->query('status')
            : 'all';

        // Base list — admin sees everyone, sub-admin only their own.
        $list = SupportQuery::with(['user:id,name,mobile,avatar_path', 'replies.user:id,name,role']);
        if (! $isAdmin) {
            $list->where('user_id', $user->id);
        }

        if ($period !== 'all') {
            $list->where('created_at', '>=', now()->subDays((int) $period));
        }

        if ($status === 'pending') {
            $list->where('status', SupportQuery::STATUS_PENDING);
        } elseif ($status === 'replied') {
            $list->where('status', SupportQuery::STATUS_APPROVED);
        }

        $queries = $list->orderByDesc('id')->get();

        // Stats stay scoped to the user (or all for admin) — independent of
        // the active chip so totals don't whip around when filtering.
        $statsScope = $isAdmin
            ? SupportQuery::query()
            : SupportQuery::where('user_id', $user->id);

        $stats = [
            'total'   => (clone $statsScope)->count(),
            'month'   => (clone $statsScope)->whereYear('created_at', now()->year)
                                            ->whereMonth('created_at', now()->month)
                                            ->count(),
            'pending' => (clone $statsScope)->where('status', SupportQuery::STATUS_PENDING)->count(),
            'replied' => (clone $statsScope)->where('status', SupportQuery::STATUS_APPROVED)->count(),
        ];

        return view('support.index', [
            'queries' => $queries,
            'stats'   => $stats,
            'isAdmin' => $isAdmin,
            'period'  => $period,
            'status'  => $status,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject'     => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'file'        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:5120'],
        ], [
            'file.mimes' => 'File must be a PDF, image (JPG/PNG/WEBP) or Word document.',
            'file.max'   => 'File must be 5MB or smaller.',
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
            'file'    => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:5120'],
        ], [
            'message.required_without' => 'Type a reply or attach a file.',
            'file.mimes' => 'File must be a PDF, image (JPG/PNG/WEBP) or Word document.',
            'file.max'   => 'File must be 5MB or smaller.',
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

        // First admin reply marks the query replied/resolved.
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
