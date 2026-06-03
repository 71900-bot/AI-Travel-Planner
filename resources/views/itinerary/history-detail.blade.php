<x-layout>
    <div class="w-full max-w-5xl mx-auto">
        <div class="mb-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-blue-700">{{ $trip->itinerary_name ?? 'Itinerary Details' }}</h1>
                    <p class="text-slate-600 mt-2">
                        {{ $trip->from_city }} -> {{ $trip->to_city }} • {{ $trip->duration }} days • {{ $trip->travelers }} traveler(s)
                    </p>
                    <p class="text-slate-600">
                        Generated: <time class="local-itinerary-time" datetime="{{ $trip->created_at->toIso8601String() }}">{{ $trip->created_at->format('F j, Y, g:i a') }}</time>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="history.back()" class="rounded-xl bg-white border border-slate-200 text-slate-800 px-4 py-2 hover:bg-slate-50 transition text-sm font-semibold">
                        Back
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white/90 backdrop-blur shadow rounded-2xl border border-slate-100 p-6">
            <div class="flex items-center justify-between gap-4 mb-4">
                <div class="text-sm text-slate-600">
                    Saved on <span class="font-semibold text-slate-800"><time class="local-itinerary-date" datetime="{{ $trip->created_at->toIso8601String() }}">{{ $trip->created_at->format('F j, Y') }}</time></span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="/trip/{{ $trip->id }}/pdf" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 text-white px-4 py-2 hover:bg-blue-700 transition text-sm font-semibold shadow-sm">
                        <span aria-hidden="true">⬇️</span>
                        Download PDF
                    </a>
                </div>
            </div>

            <div class="prose max-w-none">
                {!! \App\Helpers\ItineraryFormatter::format($trip->itinerary) !!}
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('time.local-itinerary-time, time.local-itinerary-date').forEach(function (el) {
                var iso = el.getAttribute('datetime');
                if (!iso) {
                    return;
                }
                var date = new Date(iso);
                if (isNaN(date.getTime())) {
                    return;
                }

                if (el.classList.contains('local-itinerary-time')) {
                    el.textContent = new Intl.DateTimeFormat(undefined, {
                        month: 'long',
                        day: 'numeric',
                        year: 'numeric',
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    }).format(date);
                } else {
                    el.textContent = new Intl.DateTimeFormat(undefined, {
                        month: 'long',
                        day: 'numeric',
                        year: 'numeric'
                    }).format(date);
                }
            });
        });
    </script>
</x-layout>

