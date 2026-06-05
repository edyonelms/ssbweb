<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnquiriesController extends Controller
{
    private const STATUS_OPTIONS = ['all', 'pending', 'contacted', 'approved'];
    private const PERIOD_OPTIONS = ['all', '7', '15', '30'];

    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), self::STATUS_OPTIONS, true)
            ? $request->query('status')
            : 'all';

        $period = in_array($request->query('period'), self::PERIOD_OPTIONS, true)
            ? $request->query('period')
            : 'all';

        $search = trim((string) $request->query('q', ''));

        $query = Enquiry::query()->orderByDesc('id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($period !== 'all') {
            $query->where('created_at', '>=', now()->subDays((int) $period));
        }
        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhere('phone', 'like', $like)
                  ->orWhere('subject', 'like', $like)
                  ->orWhere('message', 'like', $like);
            });
        }

        $enquiries = $query->get();

        $stats = [
            'total'     => Enquiry::count(),
            'pending'   => Enquiry::where('status', Enquiry::STATUS_PENDING)->count(),
            'contacted' => Enquiry::where('status', Enquiry::STATUS_CONTACTED)->count(),
            'approved'  => Enquiry::where('status', Enquiry::STATUS_APPROVED)->count(),
        ];

        return view('enquiries.index', [
            'enquiries' => $enquiries,
            'stats'     => $stats,
            'status'    => $status,
            'period'    => $period,
            'search'    => $search,
        ]);
    }

    public function update(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $data = $request->validate([
            'status'      => ['required', 'in:'.implode(',', Enquiry::STATUSES)],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($data['status'] !== Enquiry::STATUS_PENDING && ! $enquiry->responded_at) {
            $data['responded_at'] = now();
        }

        $enquiry->update($data);

        return redirect()
            ->route('enquiries.index')
            ->with('status', 'Enquiry updated.');
    }

    public function destroy(Enquiry $enquiry): RedirectResponse
    {
        $enquiry->delete();
        return redirect()
            ->route('enquiries.index')
            ->with('status', 'Enquiry deleted.');
    }
}
