<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your AI Travel Itinerary</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold text-blue-800">✈️ Your AI Travel Itinerary</h1>
                <a href="/" class="text-blue-600 hover:text-blue-800 font-semibold">← Back to Home</a>
            </div>
        </div>

        <!-- Itinerary Content -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-6">
            <div class="prose prose-lg max-w-none space-y-4">
                {!! \App\Helpers\ItineraryFormatter::format($data) !!}
            </div>
        </div>

        <!-- Download Button -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <a href="/download-livewire-pdf" class="block w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold py-4 px-6 rounded-xl shadow-lg transition transform hover:scale-[1.02] flex items-center justify-center gap-2 text-center">
                <span class="text-2xl">📄</span>
                <span class="text-xl">Download as PDF</span>
            </a>
        </div>
    </div>
</body>
</html>
