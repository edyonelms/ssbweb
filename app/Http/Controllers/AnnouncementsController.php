<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnnouncementsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $announcements = Announcement::with('recipients:id,name')
                ->orderByDesc('id')
                ->get();

            $subadmins = User::where('role', User::ROLE_SUBADMIN)
                ->orderBy('name')
                ->get(['id', 'name', 'mobile']);

            return view('announcements.index', [
                'announcements' => $announcements,
                'subadmins'     => $subadmins,
                'isAdmin'       => true,
            ]);
        }

        // Subadmin: announcements addressed to all OR explicitly to them.
        $announcements = Announcement::where('audience', Announcement::AUDIENCE_ALL)
            ->orWhereHas('recipients', fn ($q) => $q->where('users.id', $user->id))
            ->orderByDesc('id')
            ->get();

        return view('announcements.index', [
            'announcements' => $announcements,
            'subadmins'     => collect(),
            'isAdmin'       => false,
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

        return redirect()
            ->route('announcements.index')
            ->with('status', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        if ($announcement->file_path && Storage::disk('public')->exists($announcement->file_path)) {
            Storage::disk('public')->delete($announcement->file_path);
        }

        $announcement->delete();

        return redirect()
            ->route('announcements.index')
            ->with('status', 'Announcement deleted.');
    }

    private function validateAnnouncement(Request $request): array
    {
        return $request->validate([
            'heading'        => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:5000'],
            'file'           => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:2048'],
            'audience'       => ['required', 'in:all,selected'],
            'recipient_ids'  => ['nullable', 'array'],
            'recipient_ids.*'=> ['integer', 'exists:users,id'],
        ], [
            'file.mimes' => 'File must be a PDF, image (JPG/PNG/WEBP) or Word document.',
            'file.max'   => 'File must be 2MB or smaller.',
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
