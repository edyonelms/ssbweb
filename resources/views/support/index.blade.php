@extends('layouts.admin')

@section('title', 'Support - SSB Education')

@php
    /** @var \Illuminate\Support\Collection $queries */
    /** @var array $stats */
    /** @var bool $isAdmin */

    $reopenMode = old('panel_mode'); // 'create' or 'reply'
    $reopenQueryId = old('query_id');

    $queriesData = $queries->map(function ($q) {
        return [
            'id'                 => $q->id,
            'subject'            => $q->subject,
            'description'        => $q->description,
            'status'             => $q->status,
            'file_url'           => $q->file_url,
            'file_original_name' => $q->file_original_name,
            'user_name'          => $q->user?->name,
            'user_mobile'        => $q->user?->mobile,
            'created_at'         => $q->created_at?->format('d M Y · h:i A'),
            'replies'            => $q->replies->map(fn ($r) => [
                'id'                 => $r->id,
                'message'            => $r->message,
                'file_url'           => $r->file_url,
                'file_original_name' => $r->file_original_name,
                'author_name'        => $r->user?->name,
                'author_role'        => $r->user?->role,
                'created_at'         => $r->created_at?->format('d M Y · h:i A'),
            ])->values()->all(),
        ];
    })->keyBy('id');
@endphp

@section('admin-header')
<div class="sticky top-0 z-20 bg-white border-b border-slate-200">
    <div class="px-6 lg:px-10 py-3 flex flex-wrap items-center gap-4">
        <div class="mr-auto flex items-baseline gap-3 flex-wrap">
            <h2 class="text-base font-bold text-slate-800">Support</h2>
            <p class="text-xs text-slate-500">
                <span class="text-slate-800 font-semibold">{{ $stats['total'] }}</span> total
                <span class="text-slate-300 mx-1">·</span>
                <span class="text-slate-800 font-semibold">{{ $stats['month'] }}</span> this month
                <span class="text-slate-300 mx-1">·</span>
                <span class="text-amber-600 font-semibold">{{ $stats['pending'] }}</span> pending
                <span class="text-slate-300 mx-1">·</span>
                <span class="text-emerald-600 font-semibold">{{ $stats['approved'] }}</span> approved
            </p>
        </div>

        @if (! $isAdmin)
            <button type="button" onclick="SupportPanel.openCreate()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Contact Admin
            </button>
        @endif
    </div>
</div>
@endsection

@section('admin')
{{-- LISTING --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    @if ($queries->isEmpty())
        <div class="px-6 py-16 text-center text-sm text-slate-500">
            @if ($isAdmin)
                No queries yet.
            @else
                You haven't raised any queries yet. Click <span class="font-semibold text-pink-600">Contact Admin</span> to start.
            @endif
        </div>
    @else
        <ul class="divide-y divide-slate-100">
            @foreach ($queries as $q)
                <li class="support-row hover:bg-slate-50 transition cursor-pointer px-6 py-4"
                    data-query-id="{{ $q->id }}">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-md bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-sm font-semibold text-slate-800 truncate">{{ $q->subject }}</h3>
                                @if ($q->isPending())
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-amber-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-emerald-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Approved
                                    </span>
                                @endif
                                @if ($q->file_path)
                                    <span class="text-[10px] font-medium text-slate-500 inline-flex items-center gap-0.5">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        Attached
                                    </span>
                                @endif
                                @if ($q->replies->isNotEmpty())
                                    <span class="text-[10px] font-medium text-slate-500">· {{ $q->replies->count() }} {{ $q->replies->count() === 1 ? 'reply' : 'replies' }}</span>
                                @endif
                            </div>
                            @if ($q->description)
                                <p class="text-sm text-slate-500 mt-0.5 line-clamp-2">{{ $q->description }}</p>
                            @endif
                            <p class="text-[11px] text-slate-400 mt-1">
                                @if ($isAdmin && $q->user)
                                    {{ $q->user->name }} <span class="text-slate-300">·</span>
                                @endif
                                {{ $q->created_at?->format('d M Y · h:i A') }}
                            </p>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>

{{-- SLIDE-IN PANEL --}}
<aside id="slidePanel" class="fixed inset-0 z-30 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/30 opacity-0 transition-opacity duration-200" id="slidePanelBackdrop" onclick="SupportPanel.close()"></div>
    <div id="slidePanelCard"
         style="top: var(--topbar-h, 64px)"
         class="absolute right-0 bottom-0 w-full max-w-md bg-white border-l border-slate-200 flex flex-col translate-x-full transition-transform duration-300 ease-out">

        <div class="px-5 py-3 border-b border-slate-200 flex items-center justify-between bg-white">
            <h3 id="panelTitle" class="text-sm font-bold text-slate-800">Query</h3>
            <button type="button" onclick="SupportPanel.close()"
                    class="w-8 h-8 rounded-md text-slate-500 hover:bg-slate-100 hover:text-slate-700 inline-flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto">

            {{-- VIEW MODE (with thread) --}}
            <div id="panelView" class="panel-mode hidden p-6 space-y-5">
                <div class="pb-4 border-b border-slate-100">
                    <span id="viewStatus" class="inline-flex items-center gap-1 text-[11px] font-semibold uppercase tracking-wider"></span>
                    <h4 id="viewSubject" class="mt-1.5 text-base font-bold text-slate-800"></h4>
                    <p id="viewMeta" class="text-xs text-slate-400 mt-1"></p>
                </div>

                <div id="viewDescriptionWrap">
                    <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Message</dt>
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

                {{-- Replies thread --}}
                <div id="viewRepliesWrap" class="hidden">
                    <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2">Replies</dt>
                    <div id="viewReplies" class="space-y-3"></div>
                </div>

                @if ($isAdmin)
                    {{-- Admin reply form (inline in view) --}}
                    <form id="replyForm" method="POST" action="" enctype="multipart/form-data" autocomplete="off"
                          class="pt-4 border-t border-slate-100 space-y-3">
                        @csrf
                        <input type="hidden" name="panel_mode" value="reply">
                        <input type="hidden" name="query_id" id="replyQueryId" value="">

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Reply</label>
                            <textarea name="message" rows="3" maxlength="5000"
                                      placeholder="Write your reply..."
                                      class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400 text-sm">{{ old('panel_mode') === 'reply' ? old('message') : '' }}</textarea>
                            @if (old('panel_mode') === 'reply') @error('message')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @endif
                        </div>

                        <label class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-slate-50 border border-dashed border-slate-300 text-sm text-slate-500 hover:bg-slate-100 cursor-pointer transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <span>Attach file</span>
                            <span class="text-xs text-slate-400 ml-auto">PDF, image, DOC · 2MB max</span>
                            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" class="hidden">
                        </label>
                        @if (old('panel_mode') === 'reply') @error('file')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @endif

                        <div class="flex justify-end">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Send Reply</button>
                        </div>
                    </form>
                @endif
            </div>

            @if (! $isAdmin)
                {{-- CREATE FORM (subadmin → admin) --}}
                <form id="createForm" method="POST" action="{{ route('support.store') }}"
                      enctype="multipart/form-data" autocomplete="off"
                      class="panel-mode hidden p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="panel_mode" value="create">

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Subject <span class="text-rose-500">*</span></label>
                        <input name="subject" type="text" required maxlength="255"
                               autocomplete="off"
                               value="{{ old('panel_mode') === 'create' ? old('subject') : '' }}"
                               placeholder="What is this about?"
                               class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
                        @if (old('panel_mode') === 'create') @error('subject')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @endif
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Description</label>
                        <textarea name="description" rows="5" maxlength="5000"
                                  placeholder="Describe your issue in detail..."
                                  class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400 text-sm">{{ old('panel_mode') === 'create' ? old('description') : '' }}</textarea>
                        @if (old('panel_mode') === 'create') @error('description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @endif
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Attachment</label>
                        <label class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-slate-50 border border-dashed border-slate-300 text-sm text-slate-500 hover:bg-slate-100 cursor-pointer transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <span>Choose file</span>
                            <span class="text-xs text-slate-400 ml-auto">PDF, image, DOC · 2MB max</span>
                            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" class="hidden">
                        </label>
                        @if (old('panel_mode') === 'create') @error('file')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @endif
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" onclick="SupportPanel.close()"
                                class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-semibold transition">Cancel</button>
                        <button type="submit"
                                class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Submit</button>
                    </div>
                </form>
            @endif

        </div>
    </div>
</aside>

<script>
    window.SUPPORT_DATA = @json($queriesData);
    window.SUPPORT_REPLY_URL_TEMPLATE = @json(url('/support/__ID__/reply'));
    window.IS_ADMIN = @json($isAdmin);

    const SupportPanel = (function () {
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

        function escapeHtml(s) {
            return (s || '').replace(/[&<>"']/g, c => ({
                '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
            })[c]);
        }

        function fillView(q) {
            document.getElementById('viewSubject').textContent = q.subject;
            const metaParts = [];
            if (window.IS_ADMIN && q.user_name) metaParts.push(q.user_name);
            if (q.created_at) metaParts.push(q.created_at);
            document.getElementById('viewMeta').textContent = metaParts.join(' · ');

            const desc = document.getElementById('viewDescription');
            const descWrap = document.getElementById('viewDescriptionWrap');
            if (q.description) {
                desc.textContent = q.description;
                descWrap.classList.remove('hidden');
            } else {
                descWrap.classList.add('hidden');
            }

            const status = document.getElementById('viewStatus');
            if (q.status === 'approved') {
                status.className = 'inline-flex items-center gap-1 text-[11px] font-semibold uppercase tracking-wider text-emerald-600';
                status.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Approved';
            } else {
                status.className = 'inline-flex items-center gap-1 text-[11px] font-semibold uppercase tracking-wider text-amber-600';
                status.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending';
            }

            const fileWrap = document.getElementById('viewFileWrap');
            if (q.file_url) {
                fileWrap.classList.remove('hidden');
                document.getElementById('viewFileLink').href = q.file_url;
                document.getElementById('viewFileName').textContent = q.file_original_name || 'Download';
            } else {
                fileWrap.classList.add('hidden');
            }

            const repliesWrap = document.getElementById('viewRepliesWrap');
            const repliesEl   = document.getElementById('viewReplies');
            if (q.replies && q.replies.length) {
                repliesEl.innerHTML = q.replies.map(r => {
                    const isAdminReply = r.author_role === 'admin';
                    const bubble = isAdminReply
                        ? 'bg-pink-50 border-pink-100'
                        : 'bg-slate-50 border-slate-100';
                    let html = '<div class="rounded-lg border ' + bubble + ' p-3 space-y-1.5">';
                    html += '<div class="flex items-center justify-between gap-2">';
                    html += '<span class="text-xs font-semibold text-slate-700">' + escapeHtml(r.author_name || 'Admin') + '</span>';
                    html += '<span class="text-[10px] text-slate-400">' + escapeHtml(r.created_at || '') + '</span>';
                    html += '</div>';
                    if (r.message) {
                        html += '<p class="text-sm text-slate-800 whitespace-pre-line">' + escapeHtml(r.message) + '</p>';
                    }
                    if (r.file_url) {
                        html += '<a href="' + r.file_url + '" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-xs font-medium text-pink-600 hover:underline">';
                        html += '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>';
                        html += escapeHtml(r.file_original_name || 'attachment');
                        html += '</a>';
                    }
                    html += '</div>';
                    return html;
                }).join('');
                repliesWrap.classList.remove('hidden');
            } else {
                repliesWrap.classList.add('hidden');
            }

            // Wire admin reply form for this query
            const replyForm = document.getElementById('replyForm');
            if (replyForm) {
                replyForm.action = window.SUPPORT_REPLY_URL_TEMPLATE.replace('__ID__', q.id);
                document.getElementById('replyQueryId').value = q.id;
                replyForm.reset();
                document.getElementById('replyQueryId').value = q.id;
            }
        }

        return {
            openView: function (id) {
                const q = window.SUPPORT_DATA[id];
                if (!q) return;
                fillView(q);
                show('panelView', q.subject);
            },
            openCreate: function () {
                const f = document.getElementById('createForm');
                if (f) f.reset();
                show('createForm', 'Contact Admin');
            },
            close: close,
        };
    })();

    document.querySelectorAll('.support-row').forEach(row => {
        row.addEventListener('click', () => SupportPanel.openView(parseInt(row.dataset.queryId, 10)));
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !document.getElementById('slidePanel').classList.contains('hidden')) {
            SupportPanel.close();
        }
    });

    // Reopen on validation error or ?view= / ?panel= query param
    (function () {
        const mode = @json($reopenMode);
        const reopenId = @json($reopenQueryId);
        if (mode === 'create' && !window.IS_ADMIN) { SupportPanel.openCreate(); return; }
        if (mode === 'reply' && reopenId) { SupportPanel.openView(parseInt(reopenId, 10)); return; }
        const params = new URLSearchParams(window.location.search);
        const view = parseInt(params.get('view'), 10);
        if (view && window.SUPPORT_DATA[view]) { SupportPanel.openView(view); return; }
        if (params.get('panel') === 'create' && !window.IS_ADMIN) SupportPanel.openCreate();
    })();
</script>
@endsection
