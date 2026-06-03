{{-- Central layout used by <x-layout>. Keep this as the only layout wrapper. --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Travel Planner</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        /* small decorative background fallback */
        .bbai-bg {
            background-image:
                radial-gradient(circle at 10% 10%, rgba(59,130,246,0.20), transparent 35%),
                radial-gradient(circle at 90% 20%, rgba(99,102,241,0.18), transparent 40%),
                radial-gradient(circle at 20% 90%, rgba(16,185,129,0.14), transparent 45%);
        }
    </style>
</head>
<body class="bbai-bg min-h-screen font-sans antialiased text-slate-900">

    <main class="min-h-screen flex items-center justify-center p-4 md:p-8">
        <div class="w-full flex flex-col items-center">
            <div class="w-full max-w-5xl animate-in fade-in duration-500">
                {{ $slot }}
            </div>
            <footer class="mt-6 text-xs text-slate-500 text-center">
                ✈️ AI Travel Planner
            </footer>
        </div>
    </main>

    @livewireScripts
</body>
</html>



