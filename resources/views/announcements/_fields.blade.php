@php
    /** @var string $mode 'create' or 'edit' */
    /** @var \Illuminate\Support\Collection $subadmins */

    $sameForm = old('panel_mode') === $mode;
    $defaultAudience = $sameForm ? old('audience', 'all') : 'all';
    $oldSelected = $sameForm ? collect(old('recipient_ids', []))->map(fn ($v) => (int) $v)->all() : [];
@endphp

<div>
    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Title <span class="text-rose-500">*</span></label>
    <input name="heading" type="text" required maxlength="255"
           autocomplete="off"
           value="{{ $sameForm ? old('heading') : '' }}"
           placeholder="Announcement title"
           class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
    @if ($sameForm) @error('heading')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @endif
</div>

<div>
    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Content <span class="text-rose-500">*</span></label>
    <textarea name="description" rows="5" maxlength="5000" required
              placeholder="Write your announcement content here..."
              class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">{{ $sameForm ? old('description') : '' }}</textarea>
    @if ($sameForm) @error('description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @endif
</div>

{{-- AUDIENCE — pill tabs --}}
<div>
    <label class="block text-sm font-semibold text-slate-700 mb-2">Audience <span class="text-rose-500">*</span></label>
    <div class="grid grid-cols-2 gap-2">
        <label class="flex items-center justify-center gap-2 px-3 py-3 rounded-xl border cursor-pointer text-sm font-semibold transition
                      border-slate-200 text-slate-600 hover:border-slate-300
                      has-[:checked]:bg-pink-50 has-[:checked]:border-pink-400 has-[:checked]:text-pink-700">
            <input type="radio" name="audience" value="all" data-audience-input
                   {{ $defaultAudience === 'all' ? 'checked' : '' }}
                   class="sr-only">
            All Sub-admins
        </label>
        <label class="flex items-center justify-center gap-2 px-3 py-3 rounded-xl border cursor-pointer text-sm font-semibold transition
                      border-slate-200 text-slate-600 hover:border-slate-300
                      has-[:checked]:bg-pink-50 has-[:checked]:border-pink-400 has-[:checked]:text-pink-700">
            <input type="radio" name="audience" value="selected" data-audience-input
                   {{ $defaultAudience === 'selected' ? 'checked' : '' }}
                   class="sr-only">
            Selected
        </label>
    </div>
    @if ($sameForm) @error('audience')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @endif
</div>

<div data-recipients-block class="{{ $defaultAudience === 'selected' ? '' : 'hidden' }}">
    <div class="flex items-center justify-between mb-1.5">
        <label class="block text-sm font-semibold text-slate-700">Recipients</label>
        <div class="flex items-center gap-3 text-xs">
            <button type="button" data-recipient-select-all class="text-pink-600 hover:underline font-semibold">Select all</button>
            <button type="button" data-recipient-clear class="text-slate-500 hover:underline font-semibold">Clear</button>
        </div>
    </div>
    <input type="text" data-recipient-search placeholder="Search..."
           class="w-full px-3 py-2 mb-2 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition">
    <div class="max-h-56 overflow-y-auto border border-slate-200 rounded-xl divide-y divide-slate-100">
        @forelse ($subadmins as $u)
            <label data-recipient-row data-name="{{ strtolower($u->name) }}"
                   class="flex items-center gap-3 px-3 py-2 hover:bg-pink-50/40 cursor-pointer">
                <input type="checkbox" name="recipient_ids[]" value="{{ $u->id }}"
                       {{ in_array($u->id, $oldSelected, true) ? 'checked' : '' }}
                       class="rounded border-slate-300 text-pink-500 focus:ring-pink-300">
                <span class="text-sm text-slate-700">{{ $u->name }}</span>
                <span class="ml-auto text-xs text-slate-400">{{ $u->mobile }}</span>
            </label>
        @empty
            <div class="px-3 py-4 text-sm text-slate-400 text-center">No sub-admins yet.</div>
        @endforelse
    </div>
    @if ($sameForm) @error('recipient_ids')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @endif
</div>

{{-- ATTACHMENT — large drop zone --}}
<div>
    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
        Attachment <span class="text-xs font-normal text-slate-500">(Optional · Image or PDF, max 5MB)</span>
    </label>
    @if ($mode === 'edit')
        <div data-current-file class="mb-2 hidden flex items-center gap-2 text-xs text-slate-500">
            <span>Current:</span>
            <a data-current-file-link target="_blank" rel="noopener" href="" class="text-pink-600 font-semibold hover:underline">
                <span data-current-file-name>file</span>
            </a>
        </div>
    @endif
    <label class="flex flex-col items-center justify-center gap-1 px-4 py-6 rounded-xl border-2 border-dashed border-slate-200 hover:border-pink-300 hover:bg-pink-50/20 cursor-pointer transition text-center">
        <svg class="w-6 h-6 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
        </svg>
        <span class="text-sm font-medium text-slate-600">Click to attach file</span>
        <span data-file-name class="text-xs text-pink-600 font-semibold"></span>
        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" data-file-input class="hidden">
    </label>
    @if ($sameForm) @error('file')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @endif
</div>

<script>
    (function () {
        const block = document.currentScript.previousElementSibling.closest('form')?.querySelector('[data-recipients-block]');
        if (!block) return;
        const search = block.querySelector('[data-recipient-search]');
        const rows = block.querySelectorAll('[data-recipient-row]');
        const selectAll = block.querySelector('[data-recipient-select-all]');
        const clear = block.querySelector('[data-recipient-clear]');

        if (search) {
            search.addEventListener('input', () => {
                const q = search.value.trim().toLowerCase();
                rows.forEach(r => {
                    r.classList.toggle('hidden', q && !r.dataset.name.includes(q));
                });
            });
        }
        if (selectAll) {
            selectAll.addEventListener('click', () => {
                rows.forEach(r => {
                    if (!r.classList.contains('hidden')) {
                        r.querySelector('input[type=checkbox]').checked = true;
                    }
                });
            });
        }
        if (clear) {
            clear.addEventListener('click', () => {
                rows.forEach(r => { r.querySelector('input[type=checkbox]').checked = false; });
            });
        }
    })();
</script>
