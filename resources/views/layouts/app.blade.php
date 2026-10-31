<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'School Test Record System') }}</title>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col font-sans antialiased text-slate-800">
    <!-- Top Header (Pakistani Government School Dark Green) -->
    <header class="bg-emerald-900 text-white shadow-md z-30 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / School Title -->
                <div class="flex items-center space-x-3">
                    <button id="mobileMenuBtn" type="button" class="md:hidden p-2 rounded-md text-emerald-100 hover:text-white hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5">
                        <div class="w-10 h-10 rounded-full bg-white text-emerald-900 font-bold flex items-center justify-center border-2 border-emerald-400 shadow-sm text-sm">
                            GHS
                        </div>
                        <div>
                            <span class="font-bold text-lg tracking-tight hidden md:block leading-tight text-white">
                                    {{ config('app.name', 'GHS Maira Quick Test System') }}
                            </span>
                            <!-- Short app name/Abbreviation: visible by default, hidden on medium/large screens -->
                            <span class="font-bold text-lg tracking-tight block md:hidden leading-tight text-white">
                                {{ config('app.short_name', 'Quick Test Record System') }}
                            </span>
                            <span class="text-xs text-emerald-200 block">Daily Test & Academic Records</span>
                        </div>
                    </a>
                </div>

                <!-- User Profile & Logout -->
                <div class="flex items-center space-x-4">
                    <div class="hidden sm:flex flex-col items-center gap-0.5 mb-1 text-right">
                        <span class="text-sm font-semibold text-white">{{ auth()->user()->name }}</span>
                        <span class="text-xs text-emerald-200">
                            @if(auth()->user()->isAdmin())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-800 text-emerald-100 border border-emerald-700">Administrator</span>
                            @elseif(auth()->user()->isTeacher())
                                @php($inchargeSections = auth()->user()->inchargeOf()->with('schoolClass')->get())
                                    @if($inchargeSections->isNotEmpty())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-800 text-emerald-100 border border-emerald-700">
                                            Teacher • {{ $inchargeSections->pluck('full_name')->join(', ') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-800 text-emerald-100 border border-emerald-700">Teacher</span>
                                    @endif
                            @endif
                        </span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-2 -mb-1 border border-emerald-700 text-xs font-medium rounded-md text-emerald-100 bg-emerald-800/80 hover:bg-emerald-700 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="flex-1 flex max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 gap-6">
        <!-- Sidebar Navigation (Desktop) -->
        <aside class="hidden md:block w-64 shrink-0 no-print">
            <nav class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 space-y-1">
                <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-800' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                @if(auth()->user()->isAdmin())
                <div class="pt-3 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Administration</div>

                <a href="{{ route('classes.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('classes.*', 'sections.show') ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-800' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('classes.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Classes & Sections
                </a>

                <a href="{{ route('teachers.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('teachers.*') ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-800' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('teachers.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Teacher Accounts
                </a>

                <a href="{{ route('subjects.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('subjects.*') ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-800' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('subjects.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Subject Catalogue
                </a>
                @endif

                <div class="pt-3 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Academic Operations</div>

                <a href="{{ route('students.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('students.*') ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-800' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('students.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Students Directory
                </a>

                <a href="{{ route('tests.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('tests.*') ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-800' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('tests.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Daily Tests & Marks
                </a>

                <a href="{{ route('tests.create') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('tests.create') ? 'bg-emerald-700 text-white shadow-sm' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }}">
                    <svg class="w-5 h-5 mr-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Record New Marks
                </a>
            </nav>
        </aside>

        <!-- Mobile Navigation Menu -->
        <div id="mobileMenu" class="hidden fixed inset-0 z-40 md:hidden bg-slate-900/60 no-print">
            <div class="fixed inset-y-0 left-0 max-w-xs w-full bg-white shadow-xl p-4 flex flex-col">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                    <span class="font-bold text-emerald-900">Navigation Menu</span>
                    <button id="closeMobileMenuBtn" class="p-1 rounded text-slate-500 hover:text-slate-700">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-4 space-y-1 flex-1 overflow-y-auto">
                    <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium text-slate-800 hover:bg-emerald-50">Dashboard</a>
                    @if(auth()->user()->isAdmin())
                        <div class="pt-2 text-xs font-bold text-slate-400 uppercase">Admin</div>
                        <a href="{{ route('classes.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-slate-800 hover:bg-emerald-50">Classes & Sections</a>
                        <a href="{{ route('teachers.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-slate-800 hover:bg-emerald-50">Teachers</a>
                        <a href="{{ route('subjects.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-slate-800 hover:bg-emerald-50">Subjects</a>
                    @endif
                    <div class="pt-2 text-xs font-bold text-slate-400 uppercase">Academic</div>
                    <a href="{{ route('students.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-slate-800 hover:bg-emerald-50">Students</a>
                    <a href="{{ route('tests.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-slate-800 hover:bg-emerald-50">Daily Tests</a>
                    <a href="{{ route('tests.create') }}" class="block px-3 py-2 rounded-md text-base text-emerald-800 font-bold bg-emerald-50">Record Marks</a>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <main class="flex-1 min-w-0">
            <!-- Flash Notifications -->
            @if(session('success'))
                <div class="mb-5 rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 flex items-start no-print">
                    <svg class="w-5 h-5 text-emerald-600 mr-3 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <div class="text-sm font-medium">{{ session('success') }}</div>
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-5 rounded-lg bg-amber-50 border border-amber-200 p-4 text-amber-800 flex items-start no-print">
                    <svg class="w-5 h-5 text-amber-600 mr-3 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div class="text-sm font-medium">{{ session('warning') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 rounded-lg bg-rose-50 border border-rose-200 p-4 text-rose-800 flex items-start no-print">
                    <svg class="w-5 h-5 text-rose-600 mr-3 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <div class="text-sm font-medium">{{ session('error') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 rounded-lg bg-rose-50 border border-rose-200 p-4 text-rose-800 no-print">
                    <div class="font-semibold text-sm mb-1">Please correct the following errors:</div>
                    <ul class="list-disc pl-5 text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">@yield('title')</h1>
                    @hasSection('subtitle')
                        <p class="text-sm text-slate-500 mt-1">@yield('subtitle')</p>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    @yield('actions')
                </div>
            </div>

            <!-- Page Body -->
            @yield('content')
        </main>
    </div>

    <footer class="mt-auto bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 no-print">
        <p>{{ config('app.name', 'GHS Maira Quick Test Record System') }} &copy; {{ date('Y') }}. Designed for reliable school academic record keeping.</p>
    </footer>

    <!-- Simple JavaScript for mobile menu -->
    <script>
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const closeMobileMenuBtn = document.getElementById('closeMobileMenuBtn');

        if (mobileMenuBtn && mobileMenu && closeMobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileMenu.classList.remove('hidden');
            });
            closeMobileMenuBtn.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
            });
            mobileMenu.addEventListener('click', (e) => {
                if (e.target === mobileMenu) {
                    mobileMenu.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>
