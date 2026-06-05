<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - SSB Education</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
        html, body { height: 100%; overflow: hidden; }
    </style>
</head>
<body class="bg-white">
    <div class="relative w-screen h-screen flex items-center justify-center">
        <img src="{{ asset('images/welcome.jpg') }}"
             alt="Manglayatan University"
             class="w-full h-full object-contain">

        <a href="{{ route('login') }}"
           class="absolute bottom-8 sm:bottom-12 left-1/2 -translate-x-1/2 inline-flex items-center gap-2 px-8 py-4 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-base sm:text-lg rounded-full shadow-2xl transition-all duration-200 hover:scale-105">
            Continue to Login
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </a>
    </div>
</body>
</html>
