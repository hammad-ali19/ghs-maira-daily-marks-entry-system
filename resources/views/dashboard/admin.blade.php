@extends('layouts.app')

@section('title', 'Administrator Dashboard')
@section('subtitle', 'School overview, statistics and recent daily test activities')

@section('actions')
    <a href="{{ route('tests.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-800 hover:bg-emerald-900 text-white text-sm font-medium rounded-lg shadow-sm transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Record Marks
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Teachers Card -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Active Teachers</p>
                <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ $teacherCount }}</p>
                <a href="{{ route('teachers.index') }}" class="text-xs text-emerald-700 hover:text-emerald-900 font-medium inline-flex items-center mt-2">
                    Manage Teachers &rarr;
                </a>
            </div>
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
        </div>

        <!-- Students Card -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Enrolled Students</p>
                <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ $studentCount }}</p>
                <a href="{{ route('students.index') }}" class="text-xs text-emerald-700 hover:text-emerald-900 font-medium inline-flex items-center mt-2">
                    Manage Students &rarr;
                </a>
            </div>
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>

        <!-- Class Sections Card -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Class Sections</p>
                <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ $sectionCount }}</p>
                <a href="{{ route('classes.index') }}" class="text-xs text-emerald-700 hover:text-emerald-900 font-medium inline-flex items-center mt-2">
                    Manage Classes &rarr;
                </a>
            </div>
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
        </div>

        <!-- Subjects Card -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Subjects Catalogue</p>
                <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ $subjectCount }}</p>
                <a href="{{ route('subjects.index') }}" class="text-xs text-emerald-700 hover:text-emerald-900 font-medium inline-flex items-center mt-2">
                    Manage Subjects &rarr;
                </a>
            </div>
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Shortcuts -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('classes.create') }}" class="p-3 bg-white border border-slate-200 rounded-lg text-center hover:border-emerald-500 hover:bg-emerald-50/50 transition shadow-xs">
            <span class="block text-sm font-semibold text-slate-800">+ Add New Class</span>
            <span class="text-xs text-slate-500">Configure grade & sections</span>
        </a>
        <a href="{{ route('teachers.create') }}" class="p-3 bg-white border border-slate-200 rounded-lg text-center hover:border-emerald-500 hover:bg-emerald-50/50 transition shadow-xs">
            <span class="block text-sm font-semibold text-slate-800">+ Register Teacher</span>
            <span class="text-xs text-slate-500">Create login credentials</span>
        </a>
        <a href="{{ route('students.create') }}" class="p-3 bg-white border border-slate-200 rounded-lg text-center hover:border-emerald-500 hover:bg-emerald-50/50 transition shadow-xs">
            <span class="block text-sm font-semibold text-slate-800">+ Enroll Student</span>
            <span class="text-xs text-slate-500">B-Form & photo upload</span>
        </a>
        <a href="{{ route('subjects.create') }}" class="p-3 bg-white border border-slate-200 rounded-lg text-center hover:border-emerald-500 hover:bg-emerald-50/50 transition shadow-xs">
            <span class="block text-sm font-semibold text-slate-800">+ Add Subject</span>
            <span class="text-xs text-slate-500">Expand subject catalogue</span>
        </a>
    </div>

    <!-- Recent Daily Tests Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900">Recent Daily Tests</h3>
                <p class="text-xs text-slate-500">Latest recorded tests across all classes</p>
            </div>
            <a href="{{ route('tests.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900">
                View All Tests &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-700 font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Class & Section</th>
                        <th class="px-4 py-3 text-left">Subject</th>
                        <th class="px-4 py-3 text-left">Test Title</th>
                        <th class="px-4 py-3 text-center">Max Marks</th>
                        <th class="px-4 py-3 text-left">Recorded By</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentTests as $test)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $test->test_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-slate-700 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                    {{ $test->classSectionSubject->classSection->full_name }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-800 font-medium whitespace-nowrap">{{ $test->classSectionSubject->subject->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $test->title }}</td>
                            <td class="px-4 py-3 text-center font-bold text-slate-800 whitespace-nowrap">{{ $test->max_marks }}</td>
                            <td class="px-4 py-3 text-slate-600 text-xs whitespace-nowrap">{{ $test->creator->name }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('tests.show', $test) }}" class="text-emerald-700 hover:text-emerald-900 font-medium text-xs">View Marks</a>
                                <a href="{{ route('reports.test.pdf', $test) }}" class="text-slate-500 hover:text-slate-800 font-medium text-xs">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                No tests recorded yet. Click "Record Marks" to enter today's test.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
