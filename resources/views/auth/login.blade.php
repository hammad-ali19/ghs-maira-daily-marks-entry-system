<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name', 'GHS Maira Quick Test System') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- School Crest / Branding Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-emerald-900 text-white font-bold text-2xl border-4 border-emerald-600 shadow-md mb-3">
                GHS
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ config('app.name', 'GHS Maira Quick Test Record System') }}</h1>
            <p class="text-sm text-slate-600 mt-1">Government High School Maira &bull; Academic Portal</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200/80 p-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-6 border-b border-slate-100 pb-3">Sign in to your account</h2>

            @if(session('success'))
                <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 p-3 text-sm text-rose-800">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600 transition text-sm @error('email') border-rose-400 bg-rose-50/50 @enderror"
                        placeholder="teacher@ghsmaira.edu.pk">
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600 transition text-sm @error('password') border-rose-400 bg-rose-50/50 @enderror"
                        placeholder="••••••••">
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                        <span class="ml-2 text-sm text-slate-600">Remember me</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-emerald-800 hover:bg-emerald-900 text-white font-medium rounded-lg shadow-sm transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-600 text-sm">
                    Sign in to Portal
                </button>
            </form>
        </div>

        <div class="text-center mt-6 text-xs text-slate-500">
            For account assistance or password reset, contact the school administrator.
        </div>
    </div>
</body>
</html>
