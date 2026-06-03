<x-layout>
    <div class="w-full max-w-5xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-blue-700">Past Itineraries</h1>
            <p class="text-slate-600 mt-2">Your saved travel plans.</p>
        </div>

        <div class="bg-white/90 backdrop-blur shadow rounded-2xl border border-slate-100 p-5">
            <div class="flex items-center justify-between gap-4 mb-4">
                <div class="text-sm text-slate-600">
                    Showing <span class="font-semibold text-slate-900">{{ $trips->count() }}</span> itinerary(s)
                </div>
                <div class="text-xs text-slate-500 whitespace-nowrap">
                    Page {{ $trips->currentPage() }} of {{ $trips->lastPage() }}
                </div>
                <div>
                    <button type="button" onclick="history.back()" class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 text-slate-800 px-4 py-2 hover:bg-slate-50 transition text-sm font-semibold shadow-sm">
                        <span aria-hidden="true">⬅️</span>
                        Back
                    </button>
                </div>
            </div>

            @if ($trips->count())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($trips as $trip)
                        @php
                            $title = $trip->itinerary_name ?? ($trip->from_city . ' -> ' . $trip->to_city);
                        @endphp
                            <a href="/history/{{ $trip->id }}" class="group block">
                            <div class="h-full rounded-xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 p-4 hover:shadow-md hover:border-blue-200 transition">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-blue-600 text-white text-lg shadow-sm">✈️</span>
                                            <div class="min-w-0">
                                                <div class="font-semibold text-slate-900 leading-snug group-hover:text-blue-700">
                                                    {{ $title }}
                                                </div>
                                                <div class="text-sm text-slate-600 mt-1">
                                                    {{ $trip->duration }} days • {{ $trip->travelers }} traveler(s)
                                                </div>
                                            </div>
                                        </div>


                                    </div>

                                    <div class="text-slate-400 text-2xl leading-none">›</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $trips->links() }}
                </div>
            @else
                <div class="p-10 text-center rounded-xl border border-dashed border-slate-200 bg-slate-50">
                    <div class="mx-auto w-16 h-16 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-3xl">🗂️</div>
                    <h2 class="mt-4 text-lg font-bold text-slate-900">No itineraries found</h2>
                    <p class="mt-2 text-slate-600 text-sm">Once you generate a trip, it will appear here.</p>
                    <div class="mt-4">
                        <a href="/" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 text-white px-4 py-2 hover:bg-blue-700 transition text-sm font-semibold shadow-sm">
                            Generate your first itinerary
                        </a>
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-layout>

