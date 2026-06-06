<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnnouncementsController extends Controller
{
    /** Announcements past this age are hidden from the UI and reaped by the daily cleanup. */
    private const MAX_AGE_DAYS = 60;

    /** Allowed values for the ?period= chip in the UI. */
    private const PERIOD_OPTIONS = ['all', '7', '15', '30', '60'];

    public function index(Request $request): View
    {
        $user = $request->user();

        $period = in_array($request->query('period'), self::PERIOD_OPTIONS, true)
            ? $request->query('period')
            : 'all';

        $audienceFilter = in_array($request->query('audience'), ['all', 'broadcast', 'targeted'], true)
            ? $request->query('audience')
            : 'all';

        // Base query — admin sees everything, sub-admin only sees what's
        // addressed to them. Everything is capped at MAX_AGE_DAYS so the
        // cleanup window matches the displayed list even before cron runs.
        $base = Announcement::with('recipients:id,name')
            ->where('created_at', '>=', now()->subDays(self::MAX_AGE_DAYS))
            ->orderByDesc('id');

        if (! $user->isAdmin()) {
            $base->where(function ($q) use ($user) {
                $q->where('audience', Announcement::AUDIENCE_ALL)
                  ->orWhereHas('recipients', fn ($r) => $r->where('users.id', $user->id));
            });
            // Subadmin-side soft delete: a row in announcement_user with
            // hidden_at = now() means this subadmin removed it from their
            // own list. Admin and other subadmins are unaffected.
            $base->whereDoesntHave('recipients', function ($r) use ($user) {
                $r->where('users.id', $user->id)->whereNotNull('announcement_user.hidden_at');
            });
        }

        // Period chip narrows the window further.
        if ($period !== 'all') {
            $base->where('created_at', '>=', now()->subDays((int) $period));
        }

        // Audience chip (admin only): broadcast vs targeted.
        if ($user->isAdmin() && $audienceFilter === 'broadcast') {
            $base->where('audience', Announcement::AUDIENCE_ALL);
        } elseif ($user->isAdmin() && $audienceFilter === 'targeted') {
            $base->where('audience', Announcement::AUDIENCE_SELECTED);
        }

        $announcements = $base->get();

        // Stats are scoped to the same visibility window (60d), not the
        // currently-applied filter, so the numbers stay stable as chips toggle.
        $statsBase = Announcement::where('created_at', '>=', now()->subDays(self::MAX_AGE_DAYS));
        if (! $user->isAdmin()) {
            $statsBase->where(function ($q) use ($user) {
                $q->where('audience', Announcement::AUDIENCE_ALL)
                  ->orWhereHas('recipients', fn ($r) => $r->where('users.id', $user->id));
            });
        }
        if (! $user->isAdmin()) {
            $statsBase->whereDoesntHave('recipients', function ($r) use ($user) {
                $r->where('users.id', $user->id)->whereNotNull('announcement_user.hidden_at');
            });
        }
        $statsAll = (clone $statsBase)->get(['id', 'created_at']);
        $stats = [
            'total'      => $statsAll->count(),
            'this_month' => $statsAll->filter(fn ($a) => $a->created_at?->isSameMonth(now()))->count(),
            'last_month' => $statsAll->filter(fn ($a) => $a->created_at?->isSameMonth(now()->subMonth()))->count(),
        ];

        $subadmins = $user->isAdmin()
            ? User::where('role', User::ROLE_SUBADMIN)->orderBy('name')->get(['id', 'name', 'mobile'])
            : collect();

        return view('announcements.index', [
            'announcements'  => $announcements,
            'subadmins'      => $subadmins,
            'isAdmin'        => $user->isAdmin(),
            'period'         => $period,
            'audienceFilter' => $audienceFilter,
            'stats'          => $stats,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAnnouncement($request);

        $file = $request->file('file');
        if ($file) {
            $data['file_path'] = $file->store('uploads/announcements', 'public');
            $data['file_original_name'] = $file->getClientOriginalName();
        }

        $data['created_by'] = $request->user()->id;
        $recipientIds = $this->normalizeRecipientIds($data);

        $announcement = Announcement::create([
            'heading'            => $data['heading'],
            'description'        => $data['description'] ?? null,
            'file_path'          => $data['file_path'] ?? null,
            'file_original_name' => $data['file_original_name'] ?? null,
            'audience'           => $data['audience'],
            'created_by'         => $data['created_by'],
        ]);

        if ($announcement->audience === Announcement::AUDIENCE_SELECTED) {
            $announcement->recipients()->sync($recipientIds);
        }

        ActivityLog::record(
            'announcement.created',
            'Posted announcement "'.$announcement->heading.'"',
            $announcement,
            ['audience' => $announcement->audience]
        );

        return redirect()
            ->route('announcements.index')
            ->with('status', 'Announcement created.');
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $data = $this->validateAnnouncement($request);

        if ($request->hasFile('file')) {
            if ($announcement->file_path && Storage::disk('public')->exists($announcement->file_path)) {
                Storage::disk('public')->delete($announcement->file_path);
            }
            $file = $request->file('file');
            $data['file_path'] = $file->store('uploads/announcements', 'public');
            $data['file_original_name'] = $file->getClientOriginalName();
        }

        $recipientIds = $this->normalizeRecipientIds($data);

        $announcement->update([
            'heading'            => $data['heading'],
            'description'        => $data['description'] ?? null,
            'file_path'          => $data['file_path'] ?? $announcement->file_path,
            'file_original_name' => $data['file_original_name'] ?? $announcement->file_original_name,
            'audience'           => $data['audience'],
        ]);

        if ($announcement->audience === Announcement::AUDIENCE_SELECTED) {
            $announcement->recipients()->sync($recipientIds);
        } else {
            $announcement->recipients()->detach();
        }

        ActivityLog::record(
            'announcement.updated',
            'Updated announcement "'.$announcement->heading.'"',
            $announcement
        );

        return redirect()
            ->route('announcements.index')
            ->with('status', 'Announcement updated.');
    }

    /**
     * Subadmin-side soft delete. Records a hidden_at timestamp on the
     * pivot row so the announcement disappears from this subadmin's list
     * without affecting the admin or any other subadmin.
     */
    public function hide(Request $request, Announcement $announcement): RedirectResponse
    {
        $user = $request->user();

        // Make sure this subadmin can actually see the announcement first,
        // otherwise it makes no sense for them to "delete" it from their
        // own view.
        $isVisible = $announcement->audience === Announcement::AUDIENCE_ALL
            || $announcement->recipients()->where('users.id', $user->id)->exists();

        if (! $isVisible) {
            abort(404);
        }

        DB::table('announcement_user')->updateOrInsert(
            ['announcement_id' => $announcement->id, 'user_id' => $user->id],
            ['hidden_at' => now()]
        );

        ActivityLog::record(
            'announcement.hidden',
            'Removed announcement "'.$announcement->heading.'" from own list',
            $announcement
        );

        return redirect()
            ->route('announcements.index')
            ->with('status', 'Announcement removed from your list.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        if ($announcement->file_path && Storage::disk('public')->exists($announcement->file_path)) {
            Storage::disk('public')->delete($announcement->file_path);
        }

        $heading = $announcement->heading;
        $announcement->delete();

        ActivityLog::record(
            'announcement.deleted',
            'Deleted announcement "'.$heading.'"'
        );

        return redirect()
            ->route('announcements.index')
            ->with('status', 'Announcement deleted.');
    }

    private function validateAnnouncement(Request $request): array
    {
        return $request->validate([
            'heading'        => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:5000'],
            'file'           => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:5120'],
            'audience'       => ['required', 'in:all,selected'],
            'recipient_ids'  => ['nullable', 'array'],
            'recipient_ids.*'=> ['integer', 'exists:users,id'],
        ], [
            'file.mimes' => 'File must be a PDF, image (JPG/PNG/WEBP) or Word document.',
            'file.max'   => 'File must be 5MB or smaller.',
        ]);
    }

    private function normalizeRecipientIds(array $data): array
    {
        if (($data['audience'] ?? 'all') !== Announcement::AUDIENCE_SELECTED) {
            return [];
        }
        return collect($data['recipient_ids'] ?? [])->unique()->values()->all();
    }
}
