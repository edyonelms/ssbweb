@php
    // Welcome image is page-specific so we cache it locally instead of
    // shipping it inline with every view in the app.
    $welcomeDataUri = \Cache::rememberForever('asset:welcome', fn () =>
        'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('images/welcome.jpg')))
    );
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - SSB Education</title>

    <link rel="preconnect" href="https://cdn.tailwindcss.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="icon" type="image/png" href="{{ $logoDataUri }}">
    <link rel="shortcut icon" type="image/png" href="{{ $logoDataUri }}">
    <link rel="apple-touch-icon" href="{{ $logoDataUri }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
        html, body { height: 100%; overflow: hidden; }
        /* Keep the banner sharp when the browser scales it up to fill the viewport. */
        .splash-img { image-rendering: -webkit-optimize-contrast; image-rendering: crisp-edges; }
    </style>
</head>
<body>
    <div class="relative w-screen h-screen bg-white">
        <img src="{{ $welcomeDataUri }}" alt="Manglayatan University"
             decoding="sync" fetchpriority="high"
             class="splash-img absolute inset-0 w-full h-full object-cover">

        <a href="{{ route('login.form') }}"
           class="absolute bottom-10 sm:bottom-14 left-1/2 -translate-x-1/2 inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-fuchsia-500/90 via-pink-500/90 to-rose-500/90 hover:from-fuchsia-500 hover:via-pink-500 hover:to-rose-500 text-white font-semibold text-base sm:text-lg rounded-full shadow-2xl shadow-pink-500/30 hover:shadow-pink-500/50 transition-all duration-200 transform hover:-translate-y-0.5 hover:scale-105">
            Continue to Login
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </a>
    </div>
</body>
</html>
