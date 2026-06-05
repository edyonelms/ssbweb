<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - SSB Education</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-white min-h-screen">
    <div class="min-h-screen w-full flex flex-col items-center justify-between px-4 py-6 sm:py-10">

        <h1 class="text-2xl sm:text-4xl md:text-5xl font-extrabold text-slate-800 text-center tracking-tight">
            Welcome! Manglayatan University
        </h1>

        <div class="flex-1 w-full flex items-center justify-center my-6">
            <img src="{{ asset('images/welcome.jpg') }}"
                 alt="Manglayatan University"
                 class="max-w-full max-h-[70vh] w-auto h-auto object-contain">
        </div>

        <a href="{{ route('login') }}"
           class="inline-flex items-center gap-2 px-8 py-4 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-base sm:text-lg rounded-full shadow-lg transition-all duration-200 hover:scale-105">
            Continue to Login
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </a>
    </div>
</body>
</html>
