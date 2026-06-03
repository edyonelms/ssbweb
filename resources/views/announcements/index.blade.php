@extends('layouts.admin')

@section('title', 'Announcements - SSB Education')

@section('admin')
@php
    /** @var \Illuminate\Support\Collection $announcements */
    /** @var \Illuminate\Support\Collection $subadmins */
    /** @var bool $isAdmin */

    $reopenMode = old('panel_mode'); // 'create' or 'edit'
    $reopenAnnouncementId = old('announcement_id');

    $announcementsData = $announcements->map(function ($a) {
        return [
            'id'                  => $a->id,
            'heading'             => $a->heading,
            'description'         => $a->description,
            'audience'            => $a->audience,
            'file_url'            => $a->file_url,
            'file_original_name'  => $a->file_original_name,
            'recipient_ids'       => $a->recipients->pluck('id')->all(),
            'recipient_names'     => $a->recipients->pluck('name')->all(),
            'created_at'          => $a->created_at?->format('d M Y, h:i A'),
        ];
    })->keyBy('id');
@endphp

{{-- STICKY FULL-WIDTH HEADER --}}
<div class="-mx-6 lg:-mx-10 -mt-6 lg:-mt-10 sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm">
    <div class="px-6 lg:px-10 py-4 flex flex-wrap items-center gap-4">
        <div class="mr-auto">
            <h2 class="text-lg font-extrabold text-slate-800 leading-tight">Announcements</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ $isAdmin ? 'Broadcast to all sub-admins or a selected few.' : 'Updates from the admin team.' }}
            </p>
        </div>

        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200">
            <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            </span>
            <div class="leading-none">
                <div class="text-[10px] font-semibold tracking-wider uppercase text-slate-500">Total</div>
                <div class="text-base font-extrabold text-slate-800">{{ $announcements->count() }}</div>
            </div>
        </div>

        @if ($isAdmin)
            <button type="button" onclick="AnnouncementsPanel.openCreate()"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-fuchsia-500/90 via-pink-500/90 to-rose-500/90 hover:from-fuchsia-500 hover:via-pink-500 hover:to-rose-500 text-white text-sm font-semibold shadow-lg shadow-pink-500/20 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Announcement
            </button>
        @endif
    </div>
</div>

{{-- LISTING --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    @if ($announcements->isEmpty())
        <div class="px-6 py-16 text-center text-slate-500">
            <div class="flex flex-col items-center gap-2">
                <svg class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                <p class="text-sm">
                    @if ($isAdmin)
                        No announcements yet. Click <span class="font-semibold text-pink-600">New Announcement</span> to create one.
                    @else
                        No announcements yet.
                    @endif
                </p>
            </div>
        </div>
    @else
        <ul class="divide-y divide-slate-100">
            @foreach ($announcements as $a)
                <li class="announcement-row hover:bg-pink-50/40 transition cursor-pointer px-6 py-4"
                    data-announcement-id="{{ $a->id }}">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-fuchsia-100 to-pink-100 text-pink-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-semibold text-slate-800 truncate">{{ $a->heading }}</h3>
                                @if ($a->isForAll())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-[10px] font-bold uppercase tracking-wider">All</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-50 border border-amber-100 text-amber-700 text-[10px] font-bold uppercase tracking-wider">
                                        Selected · {{ $a->recipients->count() }}
                                    </span>
                                @endif
                                @if ($a->file_path)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 border border-slate-200 text-slate-600 text-[10px] font-semibold">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        Attachment
                                    </span>
                                @endif
                            </div>
                            @if ($a->description)
                                <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $a->description }}</p>
                            @endif
                            <p class="text-xs text-slate-400 mt-1">{{ $a->created_at?->format('d M Y, h:i A') }}</p>
                        </div>

                        @if ($isAdmin)
                            <div class="flex items-center gap-1.5" onclick="event.stopPropagation()">
                                <button type="button" onclick="AnnouncementsPanel.openEdit({{ $a->id }})"
                                        title="Edit"
                                        class="w-8 h-8 rounded-lg bg-pink-50 hover:bg-pink-100 text-pink-600 inline-flex items-center justify-center transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('announcements.destroy', $a) }}"
                                      onsubmit="return confirm('Delete this announcement?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete"
                                            class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 inline-flex items-center justify-center transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>

{{-- SLIDE-IN PANEL --}}
<aside id="slidePanel" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-200" id="slidePanelBackdrop" onclick="AnnouncementsPanel.close()"></div>
    <div id="slidePanelCard"
         class="absolute right-0 top-0 bottom-0 w-full max-w-md bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-out">

        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
            <h3 id="panelTitle" class="text-base font-extrabold text-slate-800">Announcement</h3>
            <button type="button" onclick="AnnouncementsPanel.close()"
                    class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 inline-flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto">

            {{-- VIEW MODE --}}
            <div id="panelView" class="panel-mode hidden p-6 space-y-5">
                <div>
                    <span id="viewAudience" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"></span>
                    <h4 id="viewHeading" class="mt-2 text-lg font-extrabold text-slate-800"></h4>
                    <p id="viewCreated" class="text-xs text-slate-500 mt-1"></p>
                </div>

                <div id="viewDescriptionWrap">
                    <dt class="text-[11px] font-semibold text-pink-500 uppercase tracking-wider">Description</dt>
                    <dd id="viewDescription" class="mt-1 text-slate-800 whitespace-pre-line"></dd>
                </div>

                <div id="viewFileWrap" class="hidden">
                    <dt class="text-[11px] font-semibold text-pink-500 uppercase tracking-wider">Attachment</dt>
                    <a id="viewFileLink" href="" target="_blank" rel="noopener"
                       class="mt-1 inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-sm font-semibold transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        <span id="viewFileName">Download</span>
                    </a>
                </div>

                <div id="viewRecipientsWrap" class="hidden">
                    <dt class="text-[11px] font-semibold text-pink-500 uppercase tracking-wider">Recipients</dt>
                    <div id="viewRecipients" class="mt-1 flex flex-wrap gap-1.5"></div>
                </div>

                @if ($isAdmin)
                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                        <button type="button" id="viewEditBtn"
                                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-pink-50 hover:bg-pink-100 text-pink-700 text-sm font-semibold transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </button>
                        <form id="viewDeleteForm" method="POST" action="" class="flex-1"
                              onsubmit="return confirm('Delete this announcement?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-sm font-semibold transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                Delete
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            @if ($isAdmin)
                {{-- CREATE FORM --}}
                <form id="createForm" method="POST" action="{{ route('announcements.store') }}"
                      enctype="multipart/form-data" autocomplete="off"
                      class="panel-mode hidden p-6 space-y-5">
                    @csrf
                    <input type="hidden" name="panel_mode" value="create">
                    @include('announcements._fields', ['mode' => 'create', 'subadmins' => $subadmins])
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="AnnouncementsPanel.close()"
                                class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition">Cancel</button>
                        <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-fuchsia-500/90 via-pink-500/90 to-rose-500/90 hover:from-fuchsia-500 hover:via-pink-500 hover:to-rose-500 text-white text-sm font-semibold shadow-lg shadow-pink-500/20 transition">Send</button>
                    </div>
                </form>

                {{-- EDIT FORM --}}
                <form id="editForm" method="POST" action=""
                      enctype="multipart/form-data" autocomplete="off"
                      class="panel-mode hidden p-6 space-y-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="panel_mode" value="edit">
                    <input type="hidden" name="announcement_id" id="editAnnouncementId" value="">
                    @include('announcements._fields', ['mode' => 'edit', 'subadmins' => $subadmins])
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="AnnouncementsPanel.close()"
                                class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition">Cancel</button>
                        <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-fuchsia-500/90 via-pink-500/90 to-rose-500/90 hover:from-fuchsia-500 hover:via-pink-500 hover:to-rose-500 text-white text-sm font-semibold shadow-lg shadow-pink-500/20 transition">Save Changes</button>
                    </div>
                </form>
            @endif

        </div>
    </div>
</aside>

<script>
    window.ANNOUNCEMENTS_DATA = @json($announcementsData);
    window.ANNOUNCEMENT_UPDATE_URL_TEMPLATE  = @json(url('/announcements/__ID__'));
    window.ANNOUNCEMENT_DESTROY_URL_TEMPLATE = @json(url('/announcements/__ID__'));
    window.IS_ADMIN = @json($isAdmin);

    const AnnouncementsPanel = (function () {
        const panel    = document.getElementById('slidePanel');
        const card     = document.getElementById('slidePanelCard');
        const backdrop = document.getElementById('slidePanelBackdrop');
        const title    = document.getElementById('panelTitle');
        const modes    = document.querySelectorAll('.panel-mode');

        function show(modeId, titleText) {
            modes.forEach(m => m.classList.toggle('hidden', m.id !== modeId));
            title.textContent = titleText;
            panel.classList.remove('hidden');
            panel.setAttribute('aria-hidden', 'false');
            requestAnimationFrame(() => {
                backdrop.classList.add('opacity-100');
                backdrop.classList.remove('opacity-0');
                card.classList.remove('translate-x-full');
            });
            document.body.style.overflow = 'hidden';
        }

        function close() {
            backdrop.classList.remove('opacity-100');
            backdrop.classList.add('opacity-0');
            card.classList.add('translate-x-full');
            setTimeout(() => {
                panel.classList.add('hidden');
                panel.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }, 250);
        }

        function fillView(a) {
            document.getElementById('viewHeading').textContent     = a.heading;
            document.getElementById('viewCreated').textContent     = a.created_at || '';
            const desc = document.getElementById('viewDescription');
            const descWrap = document.getElementById('viewDescriptionWrap');
            if (a.description) {
                desc.textContent = a.description;
                descWrap.classList.remove('hidden');
            } else {
                descWrap.classList.add('hidden');
            }

            const audience = document.getElementById('viewAudience');
            if (a.audience === 'all') {
                audience.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 border border-emerald-100 text-emerald-700';
                audience.textContent = 'Sent to all sub-admins';
            } else {
                audience.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-50 border border-amber-100 text-amber-700';
                audience.textContent = 'Sent to ' + (a.recipient_ids || []).length + ' sub-admin(s)';
            }

            const fileWrap = document.getElementById('viewFileWrap');
            if (a.file_url) {
                fileWrap.classList.remove('hidden');
                const link = document.getElementById('viewFileLink');
                link.href = a.file_url;
                document.getElementById('viewFileName').textContent = a.file_original_name || 'Download';
            } else {
                fileWrap.classList.add('hidden');
            }

            const recipientsWrap = document.getElementById('viewRecipientsWrap');
            const recipients = document.getElementById('viewRecipients');
            if (a.audience === 'selected' && (a.recipient_names || []).length) {
                recipients.innerHTML = '';
                a.recipient_names.forEach(n => {
                    const chip = document.createElement('span');
                    chip.className = 'inline-flex items-center px-2 py-0.5 rounded-full bg-pink-50 border border-pink-100 text-pink-700 text-xs font-semibold';
                    chip.textContent = n;
                    recipients.appendChild(chip);
                });
                recipientsWrap.classList.remove('hidden');
            } else {
                recipientsWrap.classList.add('hidden');
            }

            if (window.IS_ADMIN) {
                document.getElementById('viewDeleteForm').action = window.ANNOUNCEMENT_DESTROY_URL_TEMPLATE.replace('__ID__', a.id);
                document.getElementById('viewEditBtn').onclick = () => AnnouncementsPanel.openEdit(a.id);
            }
        }

        function setAudienceUI(form, audience) {
            const recipientsBlock = form.querySelector('[data-recipients-block]');
            if (recipientsBlock) recipientsBlock.classList.toggle('hidden', audience !== 'selected');
            form.querySelectorAll('[data-audience-input]').forEach(r => {
                r.checked = (r.value === audience);
            });
        }

        function fillEditForm(a) {
            const f = document.getElementById('editForm');
            f.action = window.ANNOUNCEMENT_UPDATE_URL_TEMPLATE.replace('__ID__', a.id);
            f.querySelector('#editAnnouncementId').value = a.id;
            f.querySelector('[name="heading"]').value     = a.heading || '';
            f.querySelector('[name="description"]').value = a.description || '';
            setAudienceUI(f, a.audience || 'all');
            const selected = new Set(a.recipient_ids || []);
            f.querySelectorAll('[name="recipient_ids[]"]').forEach(c => {
                c.checked = selected.has(parseInt(c.value, 10));
            });
            const fileInput = f.querySelector('[name="file"]');
            if (fileInput) fileInput.value = '';
            const current = f.querySelector('[data-current-file]');
            if (current) {
                if (a.file_url) {
                    current.classList.remove('hidden');
                    current.querySelector('[data-current-file-name]').textContent = a.file_original_name || 'Current attachment';
                    current.querySelector('[data-current-file-link]').href = a.file_url;
                } else {
                    current.classList.add('hidden');
                }
            }
        }

        function clearCreateForm() {
            const f = document.getElementById('createForm');
            if (!f) return;
            f.reset();
            setAudienceUI(f, 'all');
        }

        return {
            openView: function (id) {
                const a = window.ANNOUNCEMENTS_DATA[id];
                if (!a) return;
                fillView(a);
                show('panelView', a.heading);
            },
            openCreate: function () {
                clearCreateForm();
                show('createForm', 'New Announcement');
            },
            openEdit: function (id) {
                const a = window.ANNOUNCEMENTS_DATA[id];
                if (!a) return;
                fillEditForm(a);
                show('editForm', 'Edit Announcement');
            },
            close: close,
        };
    })();

    // Row click → view panel
    document.querySelectorAll('.announcement-row').forEach(row => {
        row.addEventListener('click', () => {
            AnnouncementsPanel.openView(parseInt(row.dataset.announcementId, 10));
        });
    });

    // Audience toggle inside forms
    document.querySelectorAll('[data-audience-input]').forEach(radio => {
        radio.addEventListener('change', () => {
            const form = radio.closest('form');
            const block = form.querySelector('[data-recipients-block]');
            if (block) block.classList.toggle('hidden', radio.value !== 'selected');
        });
    });

    // ESC closes
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !document.getElementById('slidePanel').classList.contains('hidden')) {
            AnnouncementsPanel.close();
        }
    });

    // Reopen on validation error or ?panel= query
    (function () {
        const mode = @json($reopenMode);
        const editId = @json($reopenAnnouncementId);
        if (window.IS_ADMIN && mode === 'create') { AnnouncementsPanel.openCreate(); return; }
        if (window.IS_ADMIN && mode === 'edit' && editId) { AnnouncementsPanel.openEdit(parseInt(editId, 10)); return; }
        const params = new URLSearchParams(window.location.search);
        const panel = params.get('panel');
        if (window.IS_ADMIN && panel === 'create') AnnouncementsPanel.openCreate();
        else if (panel === 'view') {
            const id = parseInt(params.get('id'), 10);
            if (id && window.ANNOUNCEMENTS_DATA[id]) AnnouncementsPanel.openView(id);
        }
    })();
</script>
@endsection
