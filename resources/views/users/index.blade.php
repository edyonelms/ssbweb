@extends('layouts.admin')

@section('title', 'Users - SSB Education')

@section('admin')
@php
    $reopenMode = old('panel_mode'); // 'create' or 'edit'
    $reopenUserId = old('user_id');

    $usersData = $users->map(fn ($u) => [
        'id'         => $u->id,
        'name'       => $u->name,
        'email'      => $u->email,
        'mobile'     => $u->mobile,
        'address'    => $u->address,
        'active'     => (bool) $u->active,
        'avatar_url' => $u->avatar_url,
        'created_at' => $u->created_at?->format('d M Y'),
    ])->keyBy('id');
@endphp

{{-- STICKY FULL-WIDTH HEADER (bleeds the parent padding) --}}
<div class="-mx-6 lg:-mx-10 -mt-6 lg:-mt-10 sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm">
    <div class="px-6 lg:px-10 py-4 flex flex-wrap items-center gap-4">
        <div class="mr-auto">
            <h2 class="text-lg font-extrabold text-slate-800 leading-tight">Users</h2>
            <p class="text-xs text-slate-500 mt-0.5">Sub-admins · login with mobile + password</p>
        </div>

        {{-- Analytics chips --}}
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200">
                <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M16 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </span>
                <div class="leading-none">
                    <div class="text-[10px] font-semibold tracking-wider uppercase text-slate-500">Total</div>
                    <div class="text-base font-extrabold text-slate-800">{{ $stats['total'] }}</div>
                </div>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-100">
                <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </span>
                <div class="leading-none">
                    <div class="text-[10px] font-semibold tracking-wider uppercase text-emerald-600">Active</div>
                    <div class="text-base font-extrabold text-emerald-700">{{ $stats['active'] }}</div>
                </div>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-100">
                <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div class="leading-none">
                    <div class="text-[10px] font-semibold tracking-wider uppercase text-amber-600">Pending</div>
                    <div class="text-base font-extrabold text-amber-700">{{ $stats['pending'] }}</div>
                </div>
            </div>
        </div>

        <button type="button" onclick="UsersPanel.openCreate()"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-fuchsia-500/90 via-pink-500/90 to-rose-500/90 hover:from-fuchsia-500 hover:via-pink-500 hover:to-rose-500 text-white text-sm font-semibold shadow-lg shadow-pink-500/20 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add User
        </button>
    </div>
</div>

{{-- LISTING --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-[11px] font-semibold tracking-wider uppercase text-slate-500">
                <tr>
                    <th class="text-left px-6 py-3">User</th>
                    <th class="text-left px-6 py-3">Mobile</th>
                    <th class="text-left px-6 py-3">Email</th>
                    <th class="text-left px-6 py-3">Status</th>
                    <th class="text-right px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $u)
                    <tr class="user-row hover:bg-pink-50/40 transition cursor-pointer" data-user-id="{{ $u->id }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if ($u->avatar_url)
                                    <img src="{{ $u->avatar_url }}" alt="" class="w-10 h-10 rounded-full object-cover ring-1 ring-slate-200">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-pink-100 to-rose-100 text-pink-600 font-extrabold flex items-center justify-center ring-1 ring-pink-100">
                                        {{ strtoupper(mb_substr($u->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="font-semibold text-slate-800">{{ $u->name }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $u->mobile }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $u->email ?: '—' }}</td>
                        <td class="px-6 py-4">
                            @if ($u->active)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-100 text-amber-700 text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-1.5" data-actions>
                                <button type="button" onclick="event.stopPropagation(); UsersPanel.openEdit({{ $u->id }})"
                                        title="Edit"
                                        class="w-8 h-8 rounded-lg bg-pink-50 hover:bg-pink-100 text-pink-600 inline-flex items-center justify-center transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('users.destroy', $u) }}"
                                      onsubmit="event.stopPropagation(); return confirm('Delete this user?');"
                                      onclick="event.stopPropagation()">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete"
                                            class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 inline-flex items-center justify-center transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center text-slate-500">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M16 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <p class="text-sm">No users yet. Click <span class="font-semibold text-pink-600">Add User</span> to create one.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- SLIDE-IN PANEL --}}
<aside id="slidePanel" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-200" id="slidePanelBackdrop" onclick="UsersPanel.close()"></div>
    <div id="slidePanelCard"
         class="absolute right-0 top-0 bottom-0 w-full max-w-md bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-out">

        {{-- Panel header --}}
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
            <h3 id="panelTitle" class="text-base font-extrabold text-slate-800">User</h3>
            <button type="button" onclick="UsersPanel.close()"
                    class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 inline-flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto">

            {{-- VIEW MODE --}}
            <div id="panelView" class="panel-mode hidden p-6 space-y-6">
                <div class="flex flex-col items-center text-center">
                    <div id="viewAvatarWrap" class="w-24 h-24 rounded-full overflow-hidden ring-4 ring-white shadow-md bg-gradient-to-br from-pink-100 to-rose-100 text-pink-600 font-extrabold text-2xl flex items-center justify-center">
                        <span id="viewAvatarInitial"></span>
                        <img id="viewAvatarImg" src="" alt="" class="w-full h-full object-cover hidden">
                    </div>
                    <h4 id="viewName" class="mt-3 text-lg font-extrabold text-slate-800"></h4>
                    <p id="viewMobile" class="text-sm text-slate-500 mt-0.5"></p>
                    <span id="viewStatus" class="mt-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"></span>
                </div>

                <dl class="grid grid-cols-1 gap-y-4 border-t border-slate-100 pt-5">
                    <div>
                        <dt class="text-[11px] font-semibold text-pink-500 uppercase tracking-wider">Email</dt>
                        <dd id="viewEmail" class="mt-1 text-slate-800 break-words"></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold text-pink-500 uppercase tracking-wider">Address</dt>
                        <dd id="viewAddress" class="mt-1 text-slate-800 whitespace-pre-line"></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold text-pink-500 uppercase tracking-wider">Member Since</dt>
                        <dd id="viewCreated" class="mt-1 text-slate-800"></dd>
                    </div>
                </dl>

                <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                    <button type="button" id="viewEditBtn"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-pink-50 hover:bg-pink-100 text-pink-700 text-sm font-semibold transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </button>
                    <form id="viewDeleteForm" method="POST" action="" class="flex-1"
                          onsubmit="return confirm('Delete this user?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-sm font-semibold transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>

            {{-- CREATE FORM --}}
            <form id="createForm" method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data"
                  autocomplete="off"
                  class="panel-mode hidden p-6 space-y-5">
                @csrf
                <input type="hidden" name="panel_mode" value="create">
                {{-- Honeypot fields absorb the browser's saved-login autofill so the real fields stay empty. --}}
                <input type="text" name="_hp_user" tabindex="-1" autocomplete="username" aria-hidden="true" class="hidden" value="">
                <input type="password" name="_hp_pass" tabindex="-1" autocomplete="current-password" aria-hidden="true" class="hidden" value="">
                @include('users._fields', ['mode' => 'create'])
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="UsersPanel.close()"
                            class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition">Cancel</button>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-fuchsia-500/90 via-pink-500/90 to-rose-500/90 hover:from-fuchsia-500 hover:via-pink-500 hover:to-rose-500 text-white text-sm font-semibold shadow-lg shadow-pink-500/20 transition">Create User</button>
                </div>
            </form>

            {{-- EDIT FORM --}}
            <form id="editForm" method="POST" action="" enctype="multipart/form-data"
                  autocomplete="off"
                  class="panel-mode hidden p-6 space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="panel_mode" value="edit">
                <input type="hidden" name="user_id" id="editUserId" value="">
                <input type="text" name="_hp_user" tabindex="-1" autocomplete="username" aria-hidden="true" class="hidden" value="">
                <input type="password" name="_hp_pass" tabindex="-1" autocomplete="current-password" aria-hidden="true" class="hidden" value="">
                @include('users._fields', ['mode' => 'edit'])
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="UsersPanel.close()"
                            class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition">Cancel</button>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-fuchsia-500/90 via-pink-500/90 to-rose-500/90 hover:from-fuchsia-500 hover:via-pink-500 hover:to-rose-500 text-white text-sm font-semibold shadow-lg shadow-pink-500/20 transition">Save Changes</button>
                </div>
            </form>

        </div>
    </div>
</aside>

<script>
    window.USERS_DATA = @json($usersData);
    window.USER_UPDATE_URL_TEMPLATE = @json(url('/users/__ID__'));
    window.USER_DESTROY_URL_TEMPLATE = @json(url('/users/__ID__'));

    const UsersPanel = (function () {
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
            // animate in
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

        function fillView(u) {
            document.getElementById('viewName').textContent    = u.name;
            document.getElementById('viewMobile').textContent  = u.mobile;
            document.getElementById('viewEmail').textContent   = u.email || '—';
            document.getElementById('viewAddress').textContent = u.address || '—';
            document.getElementById('viewCreated').textContent = u.created_at || '—';

            const img     = document.getElementById('viewAvatarImg');
            const initial = document.getElementById('viewAvatarInitial');
            if (u.avatar_url) {
                img.src = u.avatar_url;
                img.classList.remove('hidden');
                initial.textContent = '';
            } else {
                img.classList.add('hidden');
                initial.textContent = (u.name || '?').charAt(0).toUpperCase();
            }

            const status = document.getElementById('viewStatus');
            if (u.active) {
                status.className = 'mt-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 border border-emerald-100 text-emerald-700';
                status.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active';
            } else {
                status.className = 'mt-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 border border-amber-100 text-amber-700';
                status.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending';
            }

            document.getElementById('viewDeleteForm').action = window.USER_DESTROY_URL_TEMPLATE.replace('__ID__', u.id);
            document.getElementById('viewEditBtn').onclick = () => UsersPanel.openEdit(u.id);
        }

        function fillEditForm(u) {
            const f = document.getElementById('editForm');
            f.action = window.USER_UPDATE_URL_TEMPLATE.replace('__ID__', u.id);
            f.querySelector('#editUserId').value = u.id;
            f.querySelector('[name="name"]').value    = u.name || '';
            f.querySelector('[name="email"]').value   = u.email || '';
            f.querySelector('[name="mobile"]').value  = u.mobile || '';
            f.querySelector('[name="address"]').value = u.address || '';
            f.querySelector('[name="password"]').value = '';
            f.querySelector('[name="active"]').checked = !!u.active;
            const avatarPreview = f.querySelector('[data-avatar-preview]');
            if (avatarPreview) {
                if (u.avatar_url) {
                    avatarPreview.src = u.avatar_url;
                    avatarPreview.classList.remove('hidden');
                } else {
                    avatarPreview.classList.add('hidden');
                }
            }
            const fileInput = f.querySelector('[name="avatar"]');
            if (fileInput) fileInput.value = '';
        }

        function clearCreateForm() {
            const f = document.getElementById('createForm');
            f.reset();
            const preview = f.querySelector('[data-avatar-preview]');
            if (preview) preview.classList.add('hidden');
        }

        return {
            openView: function (id) {
                const u = window.USERS_DATA[id];
                if (!u) return;
                fillView(u);
                show('panelView', u.name);
            },
            openCreate: function () {
                clearCreateForm();
                show('createForm', 'Add User');
            },
            openEdit: function (id) {
                const u = window.USERS_DATA[id];
                if (!u) return;
                fillEditForm(u);
                show('editForm', 'Edit ' + u.name);
            },
            close: close,
        };
    })();

    // Row click → view panel
    document.querySelectorAll('.user-row').forEach(row => {
        row.addEventListener('click', () => {
            UsersPanel.openView(parseInt(row.dataset.userId, 10));
        });
    });

    // Esc closes panel
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !document.getElementById('slidePanel').classList.contains('hidden')) {
            UsersPanel.close();
        }
    });

    // Live preview for avatar uploads in panel forms
    document.querySelectorAll('[data-avatar-input]').forEach(input => {
        input.addEventListener('change', () => {
            const preview = input.closest('form').querySelector('[data-avatar-preview]');
            if (preview && input.files.length) {
                preview.src = URL.createObjectURL(input.files[0]);
                preview.classList.remove('hidden');
            }
        });
    });

    // Reopen panel on validation error, or open from ?panel= query param
    (function () {
        const mode = @json($reopenMode);
        const editId = @json($reopenUserId);
        if (mode === 'create') {
            UsersPanel.openCreate();
            return;
        }
        if (mode === 'edit' && editId) {
            UsersPanel.openEdit(parseInt(editId, 10));
            return;
        }
        const params = new URLSearchParams(window.location.search);
        const panel = params.get('panel');
        if (panel === 'create') {
            UsersPanel.openCreate();
        } else if (panel === 'edit') {
            const id = parseInt(params.get('id'), 10);
            if (id && window.USERS_DATA[id]) UsersPanel.openEdit(id);
        }
    })();
</script>
@endsection
