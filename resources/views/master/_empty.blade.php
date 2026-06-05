@php
    /** @var string $icon  e.g. 'building' | 'cap' | 'fee' */
    /** @var string $title */
    /** @var string $subtitle */
    /** @var ?string $action  'university' | 'course' | 'fee' | null (null hides CTA) */

    $iconPaths = [
        'building' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M14 9h.01M14 13h.01M14 17h.01"/>',
        'cap'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>',
        'fee'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/>',
    ];

    $ctaLabel = [
        'university' => 'Add University',
        'course'     => 'Add Course',
        'fee'        => 'Add Fee Structure',
    ];
@endphp

<div class="px-6 py-20 text-center">
    <div class="flex flex-col items-center gap-3">
        <div class="w-14 h-14 rounded-full bg-pink-50 text-pink-500 flex items-center justify-center">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                {!! $iconPaths[$icon] !!}
            </svg>
        </div>
        <h3 class="text-base font-bold text-slate-800">{{ $title }}</h3>
        <p class="text-sm text-slate-500">{{ $subtitle }}</p>
        @if ($action)
            <button type="button" onclick="MasterPanel.openCreate('{{ $action }}')"
                    class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                {{ $ctaLabel[$action] }}
            </button>
        @endif
    </div>
</div>
