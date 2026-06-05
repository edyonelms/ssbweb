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
        .bg-welcome {
            background-image: url('{{ asset('images/welcome.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
    </style>
</head>
<body class="m-0 p-0 overflow-hidden">
    <div class="bg-welcome relative w-screen h-screen flex items-end justify-center">
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

        <div class="relative z-10 mb-16 sm:mb-20">
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2 px-8 py-4 bg-white/95 hover:bg-white text-slate-800 font-semibold text-base sm:text-lg rounded-full shadow-2xl transition-all duration-200 hover:scale-105 backdrop-blur-sm">
                Continue to Login
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </div>
</body>
</html>
