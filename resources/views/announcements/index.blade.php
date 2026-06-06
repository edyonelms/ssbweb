@extends('layouts.admin')

@section('title', 'Announcements - SSB Education')

@php
    /** @var \Illuminate\Support\Collection $announcements */
    /** @var \Illuminate\Support\Collection $subadmins */
    /** @var bool $isAdmin */
    /** @var string $period */
    /** @var string $audienceFilter */
    /** @var array $stats */

    $reopenMode = old('panel_mode');
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

    $periodChips = [
        'all' => 'All',
        '7'   => '7d',
        '15'  => '15d',
        '30'  => '30d',
        '60'  => '60d',
    ];

    $audienceChips = [
        'all'       => 'All',
        'broadcast' => 'Broadcast',
        'targeted'  => 'Targeted',
    ];

    $buildUrl = function (array $overrides) use ($period, $audienceFilter) {
        $params = array_filter(array_merge([
            'period'   => $period === 'all' ? null : $period,
            'audience' => $audienceFilter === 'all' ? null : $audienceFilter,
        ], $overrides), fn ($v) => $v !== null && $v !== '');
        return route('announcements.index').($params ? '?'.http_build_query($params) : '');
    };
@endphp

@section('admin-header')
<div class="sticky top-0 z-20 bg-white border-b border-slate-200">
    {{-- Title + stats + action --}}
    <div class="px-6 lg:px-10 py-3 flex flex-wrap items-center gap-x-6 gap-y-2 border-b border-slate-100">
        <div class="mr-auto">
            <h2 class="text-base font-bold text-slate-800">Announcements</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage and publish announcements for your organization</p>
        </div>

        <div class="flex items-center gap-x-6 gap-y-1 text-xs text-slate-500 flex-wrap">
            <span>Total: <span class="text-slate-800 font-semibold ml-1">{{ $stats['total'] }}</span></span>
            <span>This Month: <span class="text-pink-600 font-semibold ml-1">{{ $stats['this_month'] }}</span></span>
            <span>Last Month: <span class="text-slate-800 font-semibold ml-1">{{ $stats['last_month'] }}</span></span>
        </div>

        @if ($isAdmin)
            <button type="button" onclick="AnnouncementsPanel.openCreate()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Announcement
            </button>
        @endif
    </div>

    {{-- Filter row --}}
    <div class="px-6 lg:px-10 py-2.5 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs">
        <div class="flex items-center gap-1.5 text-slate-500">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <span class="font-semibold text-slate-600">Filter by:</span>
        </div>

        <div class="flex items-center gap-1.5">
            <span class="text-slate-500">Period:</span>
            <div class="flex items-center gap-1">
                @foreach ($periodChips as $key => $label)
                    @php $isActive = $period === $key; @endphp
                    <a href="{{ $buildUrl(['period' => $key === 'all' ? null : $key]) }}"
                       class="px-3 py-1 rounded-full text-xs font-semibold transition
                              {{ $isActive
                                    ? 'bg-pink-600 text-white shadow-sm shadow-pink-500/30'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        @if ($isAdmin)
            <div class="flex items-center gap-1.5">
                <span class="text-slate-500">Audience:</span>
                <div class="flex items-center gap-1">
                    @foreach ($audienceChips as $key => $label)
                        @php $isActive = $audienceFilter === $key; @endphp
                        <a href="{{ $buildUrl(['audience' => $key === 'all' ? null : $key]) }}"
                           class="px-3 py-1 rounded-full text-xs font-semibold transition border
                                  {{ $isActive
                                        ? 'bg-pink-50 border-pink-300 text-pink-700'
                                        : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('admin')
{{-- LISTING --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    @if ($announcements->isEmpty())
        <div class="px-6 py-20 text-center">
            <div class="flex flex-col items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-pink-50 text-pink-500 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">No announcements yet</h3>
                <p class="text-sm text-slate-500">
                    @if ($isAdmin)
                        Create your first announcement to share important information.
                    @else
                        New announcements from the admin will appear here.
                    @endif
                </p>
                @if ($isAdmin)
                    <button type="button" onclick="AnnouncementsPanel.openCreate()"
                            class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Create Announcement
                    </button>
                @endif
            </div>
        </div>
    @else
        <ul class="divide-y divide-slate-100">
            @foreach ($announcements as $a)
                <li class="announcement-row hover:bg-slate-50 transition cursor-pointer px-6 py-4"
                    data-announcement-id="{{ $a->id }}">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-sm font-semibold text-slate-800 truncate">{{ $a->heading }}</h3>
                                @if ($a->isForAll())
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700">All</span>
                                @else
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-amber-50 text-amber-700">{{ $a->recipients->count() }} selected</span>
                                @endif
                                @if ($a->file_path)
                                    <span class="text-[10px] font-medium text-slate-500 inline-flex items-center gap-0.5">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        Attached
                                    </span>
                                @endif
                            </div>
                            @if ($a->description)
                                <p class="text-sm text-slate-500 mt-0.5 line-clamp-2">{{ $a->description }}</p>
                            @endif
                            <p class="text-[11px] text-slate-400 mt-1">{{ $a->created_at?->format('d M Y · h:i A') }}</p>
                        </div>

                        @if ($isAdmin)
                            <div class="flex items-center gap-1" onclick="event.stopPropagation()">
                                <button type="button" onclick="AnnouncementsPanel.openEdit({{ $a->id }})"
                                        title="Edit"
                                        class="w-8 h-8 rounded-md text-slate-500 hover:bg-slate-100 hover:text-pink-600 inline-flex items-center justify-center transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('announcements.destroy', $a) }}"
                                      onsubmit="return confirmAction(this, 'Delete this announcement? This action cannot be undone.', 'Delete announcement');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete"
                                            class="w-8 h-8 rounded-md text-slate-500 hover:bg-slate-100 hover:text-rose-600 inline-flex items-center justify-center transition">
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
<aside id="slidePanel" class="fixed inset-0 z-30 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/30 opacity-0 transition-opacity duration-200" id="slidePanelBackdrop" onclick="AnnouncementsPanel.close()"></div>
    <div id="slidePanelCard"
         style="top: var(--topbar-h, 64px)"
         class="absolute right-0 bottom-0 w-full max-w-xl bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-out">

        <button type="button" onclick="AnnouncementsPanel.close()" aria-label="Close"
                class="absolute top-3 right-3 z-10 w-8 h-8 rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700 inline-flex items-center justify-center transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        {{-- VIEW MODE --}}
        <div id="panelView" class="panel-mode hidden flex-1 flex flex-col min-h-0">
            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                <div class="pb-5 border-b border-slate-100">
                    <span id="viewAudience" class="text-[11px] font-semibold uppercase tracking-wider"></span>
                    <h4 id="viewHeading" class="mt-1.5 text-base font-bold text-slate-800"></h4>
                    <p id="viewCreated" class="text-xs text-slate-400 mt-1"></p>
                </div>

                <div id="viewDescriptionWrap">
                    <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Description</dt>
                    <dd id="viewDescription" class="mt-1 text-sm text-slate-800 whitespace-pre-line"></dd>
                </div>

                <div id="viewFileWrap" class="hidden">
                    <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Attachment</dt>
                    <a id="viewFileLink" href="" target="_blank" rel="noopener"
                       class="mt-1 inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        <span id="viewFileName">Download</span>
                    </a>
                </div>

                <div id="viewRecipientsWrap" class="hidden">
                    <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Recipients</dt>
                    <div id="viewRecipients" class="mt-1.5 flex flex-wrap gap-1.5"></div>
                </div>
            </div>

            @if ($isAdmin)
                <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                    <form id="viewDeleteForm" method="POST" action=""
                          onsubmit="return confirmAction(this, 'Delete this announcement? This action cannot be undone.', 'Delete announcement');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 text-sm font-semibold text-rose-600 hover:text-rose-700 transition">Delete</button>
                    </form>
                    <button type="button" id="viewEditBtn"
                            class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Edit Announcement</button>
                </div>
            @endif
        </div>

        @if ($isAdmin)
            {{-- CREATE FORM --}}
            <form id="createForm" method="POST" action="{{ route('announcements.store') }}"
                  enctype="multipart/form-data" autocomplete="off"
                  class="panel-mode hidden flex-1 flex flex-col min-h-0">
                @csrf
                <input type="hidden" name="panel_mode" value="create">
                <div class="flex-1 overflow-y-auto p-6 space-y-5">
                    @include('announcements._fields', ['mode' => 'create', 'subadmins' => $subadmins])
                </div>
                <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                    <button type="button" onclick="AnnouncementsPanel.close()"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Create Announcement</button>
                </div>
            </form>

            {{-- EDIT FORM --}}
            <form id="editForm" method="POST" action=""
                  enctype="multipart/form-data" autocomplete="off"
                  class="panel-mode hidden flex-1 flex flex-col min-h-0">
                @csrf
                @method('PUT')
                <input type="hidden" name="panel_mode" value="edit">
                <input type="hidden" name="announcement_id" id="editAnnouncementId" value="">
                <div class="flex-1 overflow-y-auto p-6 space-y-5">
                    @include('announcements._fields', ['mode' => 'edit', 'subadmins' => $subadmins])
                </div>
                <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                    <button type="button" onclick="AnnouncementsPanel.close()"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Save Changes</button>
                </div>
            </form>
        @endif
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
        const modes    = document.querySelectorAll('.panel-mode');

        function show(modeId) {
            modes.forEach(m => m.classList.toggle('hidden', m.id !== modeId));
            panel.classList.remove('hidden');
            panel.setAttribute('aria-hidden', 'false');
            requestAnimationFrame(() => {
                backdrop.classList.add('opacity-100');
                backdrop.classList.remove('opacity-0');
                card.classList.remove('translate-x-full');
            });
        }

        function close() {
            backdrop.classList.remove('opacity-100');
            backdrop.classList.add('opacity-0');
            card.classList.add('translate-x-full');
            setTimeout(() => {
                panel.classList.add('hidden');
                panel.setAttribute('aria-hidden', 'true');
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
                audience.className = 'text-[11px] font-semibold uppercase tracking-wider text-emerald-700';
                audience.textContent = 'Sent to all sub-admins';
            } else {
                audience.className = 'text-[11px] font-semibold uppercase tracking-wider text-amber-700';
                audience.textContent = 'Sent to ' + (a.recipient_ids || []).length + ' sub-admin(s)';
            }

            const fileWrap = document.getElementById('viewFileWrap');
            if (a.file_url) {
                fileWrap.classList.remove('hidden');
                document.getElementById('viewFileLink').href = a.file_url;
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
                    chip.className = 'inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs font-medium';
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
            const fileName = f.querySelector('[data-file-name]');
            if (fileName) fileName.textContent = '';
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
            const fileName = f.querySelector('[data-file-name]');
            if (fileName) fileName.textContent = '';
        }

        return {
            openView: function (id) {
                const a = window.ANNOUNCEMENTS_DATA[id];
                if (!a) return;
                fillView(a);
                show('panelView');
            },
            openCreate: function () {
                clearCreateForm();
                show('createForm');
            },
            openEdit: function (id) {
                const a = window.ANNOUNCEMENTS_DATA[id];
                if (!a) return;
                fillEditForm(a);
                show('editForm');
            },
            close: close,
        };
    })();

    document.querySelectorAll('.announcement-row').forEach(row => {
        row.addEventListener('click', () => AnnouncementsPanel.openView(parseInt(row.dataset.announcementId, 10)));
    });

    document.querySelectorAll('[data-audience-input]').forEach(radio => {
        radio.addEventListener('change', () => {
            const form = radio.closest('form');
            const block = form.querySelector('[data-recipients-block]');
            if (block) block.classList.toggle('hidden', radio.value !== 'selected');
        });
    });

    // Show the chosen file name inside the upload drop zone.
    document.querySelectorAll('[data-file-input]').forEach(input => {
        input.addEventListener('change', () => {
            const form = input.closest('form');
            const label = form.querySelector('[data-file-name]');
            if (label) label.textContent = input.files[0]?.name || '';
        });
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !document.getElementById('slidePanel').classList.contains('hidden')) {
            AnnouncementsPanel.close();
        }
    });

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
