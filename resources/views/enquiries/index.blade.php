@extends('layouts.admin')

@section('title', 'Enquiries - SSB Education')

@php
    /** @var \Illuminate\Support\Collection $enquiries */
    /** @var array $stats */
    /** @var string $status */
    /** @var string $period */
    /** @var string $search */

    $enquiriesData = $enquiries->map(fn ($e) => [
        'id'          => $e->id,
        'name'        => $e->name,
        'email'       => $e->email,
        'phone'       => $e->phone,
        'subject'     => $e->subject,
        'message'     => $e->message,
        'status'      => $e->status,
        'source'      => $e->source,
        'admin_notes' => $e->admin_notes,
        'created_at'  => $e->created_at?->format('d M Y · h:i A'),
        'responded_at'=> $e->responded_at?->format('d M Y · h:i A'),
    ])->keyBy('id');

    $statusChips = [
        'all'       => 'All',
        'pending'   => 'Pending',
        'contacted' => 'Contacted',
        'approved'  => 'Approved',
    ];

    $periodChips = [
        'all' => 'All',
        '7'   => '7d',
        '15'  => '15d',
        '30'  => '30d',
    ];

    $buildUrl = function (array $overrides) use ($status, $period, $search) {
        $params = array_filter(array_merge([
            'status' => $status === 'all' ? null : $status,
            'period' => $period === 'all' ? null : $period,
            'q'      => $search !== '' ? $search : null,
        ], $overrides), fn ($v) => $v !== null && $v !== '');
        return route('enquiries.index').($params ? '?'.http_build_query($params) : '');
    };
@endphp

@section('admin-header')
<div class="sticky top-0 z-20 bg-white border-b border-slate-200">
    {{-- Title + stats --}}
    <div class="px-6 lg:px-10 py-3 flex flex-wrap items-center gap-x-6 gap-y-2 border-b border-slate-100">
        <div class="mr-auto">
            <h2 class="text-base font-bold text-slate-800">Enquiries</h2>
            <p class="text-xs text-slate-500 mt-0.5">Leads captured from the marketing site contact form</p>
        </div>
        <div class="flex items-center gap-x-6 gap-y-1 text-xs text-slate-500 flex-wrap">
            <span>Total: <span class="text-slate-800 font-semibold ml-1">{{ $stats['total'] }}</span></span>
            <span>Pending: <span class="text-amber-600 font-semibold ml-1">{{ $stats['pending'] }}</span></span>
            <span>Contacted: <span class="text-sky-600 font-semibold ml-1">{{ $stats['contacted'] }}</span></span>
            <span>Approved: <span class="text-emerald-600 font-semibold ml-1">{{ $stats['approved'] }}</span></span>
        </div>
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
            <span class="text-slate-500">Status:</span>
            <div class="flex items-center gap-1">
                @foreach ($statusChips as $key => $label)
                    @php $isActive = $status === $key; @endphp
                    <a href="{{ $buildUrl(['status' => $key === 'all' ? null : $key]) }}"
                       class="px-3 py-1 rounded-full text-xs font-semibold transition
                              {{ $isActive ? 'bg-pink-600 text-white shadow-sm shadow-pink-500/30' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-1.5">
            <span class="text-slate-500">Period:</span>
            <div class="flex items-center gap-1">
                @foreach ($periodChips as $key => $label)
                    @php $isActive = $period === $key; @endphp
                    <a href="{{ $buildUrl(['period' => $key === 'all' ? null : $key]) }}"
                       class="px-3 py-1 rounded-full text-xs font-semibold transition border
                              {{ $isActive ? 'bg-pink-50 border-pink-300 text-pink-700' : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        <form method="GET" action="{{ route('enquiries.index') }}" class="ml-auto flex items-center gap-2">
            @if ($status !== 'all')<input type="hidden" name="status" value="{{ $status }}">@endif
            @if ($period !== 'all')<input type="hidden" name="period" value="{{ $period }}">@endif
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
                </div>
                <input type="text" name="q" value="{{ $search }}" placeholder="Search name, email, message..."
                       class="w-60 sm:w-72 pl-7 pr-3 py-1.5 bg-white border border-slate-200 rounded-full text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 transition">
            </div>
            <button type="submit" class="px-3 py-1.5 rounded-full text-xs font-semibold bg-pink-600 hover:bg-pink-700 text-white transition">Search</button>
            @if ($search !== '')
                <a href="{{ $buildUrl(['q' => null]) }}" class="px-2 py-1.5 rounded-full text-xs font-semibold text-slate-500 hover:bg-slate-100 transition" title="Clear search">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
        </form>
    </div>
</div>
@endsection

@section('admin')
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    @if ($enquiries->isEmpty())
        <div class="px-6 py-20 text-center">
            <div class="flex flex-col items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-pink-50 text-pink-500 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.84L3 20l1.13-3.39A7.94 7.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">No enquiries found</h3>
                <p class="text-sm text-slate-500">When visitors fill the contact form on the marketing site, their messages land here.</p>
            </div>
        </div>
    @else
        <ul class="divide-y divide-slate-100">
            @foreach ($enquiries as $e)
                <li class="enquiry-row hover:bg-slate-50 transition cursor-pointer px-6 py-4" data-enquiry-id="{{ $e->id }}">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-pink-50 text-pink-600 font-bold text-sm flex items-center justify-center shrink-0 mt-0.5">{{ strtoupper(mb_substr($e->name, 0, 1)) }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-sm font-semibold text-slate-800 truncate">{{ $e->name }}</h3>
                                @if ($e->status === 'pending')
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-amber-50 text-amber-700">Pending</span>
                                @elseif ($e->status === 'contacted')
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-sky-50 text-sky-700">Contacted</span>
                                @else
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700">Approved</span>
                                @endif
                                @if ($e->subject)
                                    <span class="text-[11px] text-slate-500">· {{ $e->subject }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-slate-500 mt-0.5 line-clamp-2">{{ $e->message }}</p>
                            <p class="text-[11px] text-slate-400 mt-1">
                                @if ($e->email)<span>{{ $e->email }}</span> <span class="text-slate-300">·</span> @endif
                                @if ($e->phone)<span>{{ $e->phone }}</span> <span class="text-slate-300">·</span> @endif
                                {{ $e->created_at?->format('d M Y · h:i A') }}
                            </p>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>

@endsection

@section('slide-panel')
{{-- SLIDE-IN PANEL --}}
<aside id="enquiryPanel" class="absolute inset-0 z-30 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/30 opacity-0 transition-opacity duration-200" id="enquiryBackdrop" onclick="EnquiryPanel.close()"></div>
    <div id="enquiryPanelCard"
         class="absolute right-0 top-0 bottom-0 w-full max-w-xl bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-out">

        <button type="button" onclick="EnquiryPanel.close()" aria-label="Close"
                class="absolute top-3 right-3 z-10 w-8 h-8 rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700 inline-flex items-center justify-center transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="flex-1 overflow-y-auto p-6 space-y-5">
            <div class="pb-4 border-b border-slate-100">
                <span id="viewStatus" class="text-[10px] font-semibold uppercase tracking-wider px-1.5 py-0.5 rounded"></span>
                <h4 id="viewName" class="mt-1.5 text-base font-bold text-slate-800"></h4>
                <p id="viewMeta" class="text-xs text-slate-400 mt-1"></p>
            </div>

            <dl class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Email</dt>
                    <dd id="viewEmail" class="mt-0.5 text-sm text-slate-800 break-words"></dd>
                </div>
                <div>
                    <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Phone</dt>
                    <dd id="viewPhone" class="mt-0.5 text-sm text-slate-800"></dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Subject</dt>
                    <dd id="viewSubject" class="mt-0.5 text-sm text-slate-800"></dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Message</dt>
                    <dd id="viewMessage" class="mt-0.5 text-sm text-slate-800 whitespace-pre-line"></dd>
                </div>
            </dl>

            <form id="enquiryForm" method="POST" action="" class="pt-4 border-t border-slate-100 space-y-3">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach (['pending' => 'Pending', 'contacted' => 'Contacted', 'approved' => 'Approved'] as $k => $l)
                            <label class="flex items-center justify-center gap-2 px-2 py-2 rounded-lg border cursor-pointer text-xs font-semibold transition
                                          border-slate-200 text-slate-600 hover:border-slate-300
                                          has-[:checked]:bg-pink-50 has-[:checked]:border-pink-400 has-[:checked]:text-pink-700">
                                <input type="radio" name="status" value="{{ $k }}" class="sr-only">
                                {{ $l }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Internal Notes</label>
                    <textarea name="admin_notes" rows="3" maxlength="2000"
                              placeholder="Add a follow-up note (visible to admins only)"
                              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm"></textarea>
                </div>
            </form>

            <form id="deleteForm" method="POST" action="" class="hidden"
                  onsubmit="return confirmAction(this, 'Delete this enquiry? This action cannot be undone.', 'Delete enquiry');">
                @csrf @method('DELETE')
            </form>
        </div>

        <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
            <button type="submit" form="deleteForm"
                    class="px-4 py-2 text-sm font-semibold text-rose-600 hover:text-rose-700 transition">Delete</button>
            <button type="submit" form="enquiryForm"
                    class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Save Changes</button>
        </div>
    </div>
</aside>

<script>
    window.ENQUIRIES_DATA = @json($enquiriesData);
    window.ENQUIRY_UPDATE_URL  = @json(url('/enquiries/__ID__'));
    window.ENQUIRY_DESTROY_URL = @json(url('/enquiries/__ID__'));

    const EnquiryPanel = (function () {
        const panel    = document.getElementById('enquiryPanel');
        const card     = document.getElementById('enquiryPanelCard');
        const backdrop = document.getElementById('enquiryBackdrop');

        function show() {
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

        return {
            openView: function (id) {
                const e = window.ENQUIRIES_DATA[id];
                if (!e) return;
                document.getElementById('viewName').textContent     = e.name;
                document.getElementById('viewEmail').textContent    = e.email || '—';
                document.getElementById('viewPhone').textContent    = e.phone || '—';
                document.getElementById('viewSubject').textContent  = e.subject || '—';
                document.getElementById('viewMessage').textContent  = e.message || '';
                document.getElementById('viewMeta').textContent     = (e.created_at || '') + ' · via ' + (e.source || 'web');

                const statusBadge = document.getElementById('viewStatus');
                const map = {
                    pending:   ['Pending',   'bg-amber-50 text-amber-700'],
                    contacted: ['Contacted', 'bg-sky-50 text-sky-700'],
                    approved:  ['Approved',  'bg-emerald-50 text-emerald-700'],
                };
                const [label, cls] = map[e.status] || ['Pending', 'bg-amber-50 text-amber-700'];
                statusBadge.textContent = label;
                statusBadge.className = 'text-[10px] font-semibold uppercase tracking-wider px-1.5 py-0.5 rounded ' + cls;

                const form = document.getElementById('enquiryForm');
                form.action = window.ENQUIRY_UPDATE_URL.replace('__ID__', e.id);
                form.querySelectorAll('[name="status"]').forEach(r => r.checked = (r.value === e.status));
                form.querySelector('[name="admin_notes"]').value = e.admin_notes || '';

                const del = document.getElementById('deleteForm');
                del.action = window.ENQUIRY_DESTROY_URL.replace('__ID__', e.id);

                show();
            },
            close,
        };
    })();

    document.querySelectorAll('.enquiry-row').forEach(row => {
        row.addEventListener('click', () => EnquiryPanel.openView(parseInt(row.dataset.enquiryId, 10)));
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !document.getElementById('enquiryPanel').classList.contains('hidden')) EnquiryPanel.close();
    });
</script>
@endsection
