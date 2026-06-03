<x-layout>
    <div class="w-full max-w-5xl mx-auto">
        <div class="mb-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-emerald-700">Admin Dashboard</h1>
                    <p class="text-slate-600 mt-2">Manage users and delete itinerary history.</p>
                </div>

                <div class="text-sm text-slate-700">
                    Signed in as <span class="font-semibold text-slate-900">{{ auth()->user()->name }}</span>
                </div>
            </div>

            <div class="mt-4 flex items-center gap-3">
                <form method="POST" action="/signout">
                    @csrf
                    <button type="submit"
                            class="rounded-xl bg-rose-600 text-white px-4 py-2 hover:bg-rose-700 transition text-sm font-semibold shadow-sm hover:shadow focus:outline-none focus:ring-2 focus:ring-rose-300">
                        <span class="inline-flex items-center gap-2">
                            <span aria-hidden="true">🚪</span>
                            <span>Sign out</span>
                        </span>
                    </button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-3 text-green-800 text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white/90 backdrop-blur shadow rounded-2xl border border-slate-100 p-5">
            <div class="flex items-center justify-between gap-4 mb-4">
                <div class="text-sm text-slate-600">
                    Showing <span class="font-semibold text-slate-900">{{ $users->count() }}</span> user(s)
                </div>
                <div class="text-xs text-slate-500 whitespace-nowrap">Page {{ $users->currentPage() }} of {{ $users->lastPage() }}</div>
            </div>

            @if ($users->count())
                <div class="space-y-4">
                    @foreach ($users as $user)
                        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between border-b border-slate-200 pb-4">
                                <div class="space-y-2">
                                    <div class="text-lg font-semibold text-slate-900">{{ $user->name }}</div>
                                    <div class="text-sm text-slate-600">{{ $user->email }}</div>
                                    <div class="text-xs text-slate-500">Showing {{ $user->trips->count() }} recent trip{{ $user->trips->count() === 1 ? '' : 's' }}</div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold {{ $user->is_admin ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-50 text-slate-600 border border-slate-200' }}">
                                        {{ $user->is_admin ? 'Admin' : 'User' }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 space-y-3">
                                @forelse ($user->trips as $trip)
                                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:flex sm:items-center sm:justify-between gap-4">
                                        <div class="min-w-0">
                                            <div class="font-semibold text-slate-900 truncate">{{ $trip->itinerary_name ?? 'Untitled itinerary' }}</div>
                                            <div class="text-sm text-slate-500 mt-1">
                                                {{ $trip->from_city ?? '' }}{{ $trip->from_city && $trip->to_city ? ' → ' : '' }}{{ $trip->to_city ?? '' }}
                                                @if ($trip->duration || $trip->travelers)
                                                    ·
                                                @endif
                                                @if ($trip->duration)
                                                    {{ $trip->duration }} day{{ $trip->duration === 1 ? '' : 's' }}
                                                @endif
                                                @if ($trip->duration && $trip->travelers)
                                                    ,
                                                @endif
                                                @if ($trip->travelers)
                                                    {{ $trip->travelers }} traveler{{ $trip->travelers === 1 ? '' : 's' }}
                                                @endif
                                            </div>
                                        </div>

                                        <form method="POST" action="/admin/trips/{{ $trip->id }}/delete" onsubmit="return confirm('Delete this itinerary trip? This will soft-delete it.');" class="mt-3 sm:mt-0">
                                            @csrf
                                            <button type="submit"
                                                    class="rounded-full bg-rose-600 text-white px-4 py-2 hover:bg-rose-700 transition text-sm font-semibold shadow-sm hover:shadow focus:outline-none focus:ring-2 focus:ring-rose-300">
                                                Delete this trip
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-4 text-slate-600">
                                        No trips found for this user.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $users->links() }}
                </div>
            @else
                <div class="p-10 text-center rounded-xl border border-dashed border-slate-200 bg-slate-50">
                    <div class="mx-auto w-16 h-16 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-3xl">👥</div>
                    <h2 class="mt-4 text-lg font-bold text-slate-900">No users found</h2>
                </div>
            @endif
        </div>
    </div>
</x-layout>

