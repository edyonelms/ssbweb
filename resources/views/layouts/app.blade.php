<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    {{-- viewport-fit=cover so iOS notch / Android gesture bar respect the
         safe-area-inset CSS env() vars used by the bottom nav. --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- "Mobile app" feel — hides the URL bar on iOS, paints the OS chrome
         to match the brand pink, prevents auto-zoom on input focus. --}}
    <meta name="theme-color" content="#ec4899">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SSB Education">
    <meta name="format-detection" content="telephone=no">

    <title>@yield('title', 'SSB Education')</title>

    {{-- Warm up third-party origins so CSS/fonts/icons aren't gated on TLS --}}
    <link rel="preconnect" href="https://cdn.tailwindcss.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.tailwindcss.com">

    {{-- Favicon is inlined as a data URI so the tab icon paints with the
         HTML — no /images/logo.png round-trip on any page. --}}
    <link rel="icon" type="image/png" href="{{ $logoDataUri }}">
    <link rel="shortcut icon" type="image/png" href="{{ $logoDataUri }}">
    <link rel="apple-touch-icon" href="{{ $logoDataUri }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Hooked by .pb-safe / .pt-safe and the bottom nav. iOS sets
               these env() vars on iPhones with a notch / home indicator;
               on Android they're 0. */
            --safe-top:    env(safe-area-inset-top, 0px);
            --safe-bottom: env(safe-area-inset-bottom, 0px);
            --safe-left:   env(safe-area-inset-left, 0px);
            --safe-right:  env(safe-area-inset-right, 0px);
        }
        body {
            font-family: 'Inter', sans-serif;
            /* Stop iOS Safari from briefly highlighting buttons grey on tap. */
            -webkit-tap-highlight-color: transparent;
            /* Inputs at <16px font-size cause iOS to zoom in on focus.
               We compensate per-field via Tailwind text-base where the
               UI demands it, but block the default zoom behavior here. */
            -webkit-text-size-adjust: 100%;
        }
        /* iOS momentum scrolling for any pane the app makes scrollable. */
        .ios-scroll { -webkit-overflow-scrolling: touch; }

        /* Safe-area helpers — bottom nav, full-screen modals, etc. */
        .pb-safe { padding-bottom: max(var(--safe-bottom), 0.5rem); }
        .pt-safe { padding-top:    var(--safe-top); }
        .pl-safe { padding-left:   var(--safe-left); }
        .pr-safe { padding-right:  var(--safe-right); }
        .mb-safe { margin-bottom:  var(--safe-bottom); }

        /* Native-app-style pull-to-refresh: disable browser-native PTR so
           the page doesn't bounce, our content area handles scroll. */
        html, body { overscroll-behavior-y: none; }

        /* Tap targets — anything we mark with .tap is comfortably hittable
           on a phone (>=44px) without bloating the desktop UI. */
        .tap { min-width: 44px; min-height: 44px; }

        /* The bell / activity feed list scrolls smoother with momentum on iOS. */
        #activityPanel .overflow-y-auto, .scroll-touch { -webkit-overflow-scrolling: touch; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">
    @yield('content')
</body>
</html>
