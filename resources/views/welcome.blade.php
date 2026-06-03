<x-layout>
    <div class="w-full max-w-7xl mx-auto p-6 md:p-8">
        <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white shadow-lg flex items-center justify-center text-xl">✈️</div>
                <h1 class="text-4xl md:text-5xl font-bold text-blue-700">
                    AI Travel Planner
                </h1>
            </div>

            <nav class="flex items-center gap-3">

                @auth
                    <a href="/history"
                       class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 text-slate-800 px-4 py-2 hover:bg-slate-50 transition text-sm font-semibold shadow-sm">
                        <span aria-hidden="true">🕘</span>
                        <span>Past Itineraries</span>
                    </a>

                    <div class="text-sm text-slate-700">
                        Signed in as <span class="font-semibold">{{ auth()->user()->name }}</span>
                    </div>
                    <form method="POST" action="/signout">
                        @csrf
                        <button class="rounded-xl bg-rose-600 text-white px-4 py-2 hover:bg-rose-700 transition text-sm font-semibold shadow-sm hover:shadow transition-all focus:outline-none focus:ring-2 focus:ring-rose-300" type="submit">
                            <span class="inline-flex items-center gap-2">
                                <span aria-hidden="true">🚪</span>
                                <span>Sign out</span>
                            </span>
                        </button>

                    </form>
                @else
                    <a class="rounded-xl bg-white border border-slate-200 text-slate-800 px-4 py-2 hover:bg-slate-50 transition text-sm font-semibold" href="/signin">
                        Sign in
                    </a>
                    <a class="rounded-xl bg-blue-600 text-white px-4 py-2 hover:bg-blue-700 transition text-sm font-semibold" href="/signup">
                        Sign up
                    </a>
                @endauth
            </nav>
        </header>

        @if (session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800 text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

@livewire('travel-form')
    </div>
</x-layout>

