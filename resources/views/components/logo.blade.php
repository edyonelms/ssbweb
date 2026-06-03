@php $id = 'lotus_' . \Illuminate\Support\Str::random(6); @endphp
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="none" {{ $attributes }}>
    <defs>
        <linearGradient id="{{ $id }}" x1="50%" y1="0%" x2="50%" y2="100%">
            <stop offset="0%" stop-color="#fda4af"/>
            <stop offset="55%" stop-color="#ec4899"/>
            <stop offset="100%" stop-color="#9d174d"/>
        </linearGradient>
    </defs>
    <g transform="translate(32 40)">
        <ellipse cx="0" cy="-12" rx="5" ry="20" fill="url(#{{ $id }})" opacity="0.95"/>
        <ellipse cx="0" cy="-12" rx="5" ry="18" fill="url(#{{ $id }})" opacity="0.85" transform="rotate(-28)"/>
        <ellipse cx="0" cy="-12" rx="5" ry="18" fill="url(#{{ $id }})" opacity="0.85" transform="rotate(28)"/>
        <ellipse cx="0" cy="-10" rx="5" ry="16" fill="url(#{{ $id }})" opacity="0.75" transform="rotate(-56)"/>
        <ellipse cx="0" cy="-10" rx="5" ry="16" fill="url(#{{ $id }})" opacity="0.75" transform="rotate(56)"/>
        <ellipse cx="0" cy="-8" rx="4" ry="13" fill="url(#{{ $id }})" opacity="0.65" transform="rotate(-82)"/>
        <ellipse cx="0" cy="-8" rx="4" ry="13" fill="url(#{{ $id }})" opacity="0.65" transform="rotate(82)"/>
        <circle cx="0" cy="0" r="3" fill="#9d174d"/>
    </g>
</svg>
