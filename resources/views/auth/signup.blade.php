<x-layout>
<div class="w-full max-w-md mx-auto">

        {{-- Header --}}
        <div class="mb-8 text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-600 text-white shadow-lg">
                ✨
            </div>

            <h1 class="text-3xl font-bold text-blue-700 mt-3">
                Create your account
            </h1>

            <p class="text-slate-600 mt-2">
                Sign up to save your itineraries.
            </p>
        </div>

        {{-- Form --}}
        <form method="POST" action="/signup"
              class="bg-white/90 backdrop-blur shadow rounded-2xl p-6 border border-slate-100 hover:shadow-xl transition">

            @csrf

            {{-- Errors --}}
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

            {{-- Name --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="name">
                    Name
                </label>

                <input id="name" name="name" value="{{ old('name') }}" type="text"
                       class="w-full rounded-xl border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring focus:ring-blue-200 outline-none px-3 py-2"
                       placeholder="Your name" required autofocus>
            </div>

            {{-- Email --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="email">
                    Email
                </label>

                <input id="email" name="email" value="{{ old('email') }}" type="email"
                       class="w-full rounded-xl border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring focus:ring-blue-200 outline-none px-3 py-2"
                       placeholder="you@example.com" required>
            </div>

            {{-- Password --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="password">
                    Password
                </label>

                <input id="password" name="password" type="password"
                       class="w-full rounded-xl border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring focus:ring-blue-200 outline-none px-3 py-2"
                       placeholder="Minimum 8 characters" required>
            </div>

            {{-- Confirm Password --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="password_confirmation">
                    Confirm password
                </label>

                <input id="password_confirmation" name="password_confirmation" type="password"
                       class="w-full rounded-xl border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring focus:ring-blue-200 outline-none px-3 py-2"
                       placeholder="Re-enter password" required>
            </div>

            {{-- Button (MATCHED SIZE WITH SIGN IN) --}}
            <button type="submit"
                    class="w-full rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 transition shadow-sm hover:shadow focus:outline-none focus:ring-2 focus:ring-blue-300">
                <span class="inline-flex items-center justify-center gap-2">
                    ✨
                    <span>Sign up</span>
                </span>
            </button>

            {{-- Footer --}}
            <div class="mt-5 text-center text-sm text-slate-600">
                Already have an account?
                <a class="text-blue-700 font-semibold hover:underline" href="/signin">
                    Sign in
                </a>
            </div>

        </form>
    </div>
</x-layout>
