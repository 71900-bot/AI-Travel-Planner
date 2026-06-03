<x-layout>
    <div class="w-full max-w-md mx-auto">
        <div class="mb-8 text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-600 text-white shadow-lg">
                🌙
            </div>
            <h1 class="text-3xl font-bold text-blue-700 mt-3">Welcome back</h1>
            <p class="text-slate-600 mt-2">Sign in to continue.</p>
        </div>

        <form method="POST" action="/signin"
              class="bg-white/90 backdrop-blur shadow rounded-2xl p-6 border border-slate-100 hover:shadow-xl transition">

            @csrf

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-3 text-red-700 text-sm">
                    <ul class="list-disc ml-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-3 text-green-800 text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="email">Email</label>
                <input id="email" name="email" value="{{ old('email') }}" type="email"
                       class="w-full rounded-xl border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring focus:ring-blue-200 outline-none px-3 py-2"
                       placeholder="you@example.com" required autofocus>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 mb-2" for="password">Password</label>

                <input id="password" name="password" type="password"
                       class="w-full rounded-xl border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring focus:ring-blue-200 outline-none px-3 py-2"
                       placeholder="Your password" required>
            </div>

            <button type="submit"
                class="w-full rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 transition shadow-sm hover:shadow focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <span class="inline-flex items-center justify-center gap-2">
                        <span aria-hidden="true">🔑</span>
                        <span>Sign in</span>
                    </span>
            </button>

            <div class="mt-5 text-center text-sm text-slate-600">
                Don’t have an account?
                <a class="text-blue-700 font-semibold hover:underline" href="/signup">Sign up</a>
            </div>
        </form>
    </div>
</x-layout>


