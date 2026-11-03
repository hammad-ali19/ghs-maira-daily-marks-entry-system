@extends('layouts.app')

@section('title', 'Student Test History: ' . $student->name)
@section('subtitle', 'Complete chronological academic test history across all classes attended')

@section('actions')
    <a href="{{ route('students.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
        &larr; Back to Students Directory
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Student Profile Overview Banner -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex items-center space-x-4">
                @if($student->photo_path)
                    <img src="{{ app()->environment('production') ? $student->photo_path : asset('storage/' . $student->photo_path) }}" alt="{{ $student->name }}" class="w-18 h-18 rounded-full object-cover border-4 border-emerald-600 shadow-md">
                @else
                    <div class="w-18 h-18 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-2xl border-4 border-emerald-600">
                        {{ substr($student->name, 0, 1) }}
                    </div>
                @endif
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">{{ $student->name }}</h2>
                    <p class="text-xs font-mono text-slate-500 mt-0.5">B-Form / CNIC: <strong class="text-slate-800">{{ $student->b_form_number }}</strong></p>
                    <div class="flex items-center space-x-3 mt-1.5 text-xs">
                        <span class="text-slate-600">Current Class: <strong>{{ $student->currentEnrollment?->classSection->full_name ?? 'Not Enrolled' }}</strong></span>
                        <span>&bull;</span>
                        <span>Status: 
                            @if($student->is_active)
                                <span class="text-emerald-700 font-bold">Active</span>
                            @else
                                <span class="text-slate-500 font-bold">Inactive</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stats badges -->
            <div class="flex items-center gap-4 bg-slate-50 border border-slate-200 rounded-xl p-3 px-5">
                <div class="text-center">
                    <span class="block text-xs uppercase font-bold text-slate-400">Total Tests</span>
                    <span class="text-2xl font-extrabold text-slate-900">{{ $totalTests }}</span>
                </div>
                <div class="h-8 w-px bg-slate-200"></div>
                <div class="text-center">
                    <span class="block text-xs uppercase font-bold text-slate-400">Overall Avg</span>
                    <span class="text-2xl font-extrabold {{ $averagePercentage >= 50 ? 'text-emerald-700' : 'text-amber-600' }}">
                        {{ $averagePercentage }}%
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar (Subject & Date Range) -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('students.history', $student) }}" class="grid grid-cols-1 sm:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Filter by Subject</label>
                <select name="subject_id" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600">
                    <option value="">-- All Subjects --</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>
                            {{ $sub->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Report Type</label>
                <div class="space-y-1.5">
                    <label class="flex items-center gap-2 text-xs cursor-pointer">
                        <input type="radio" name="report_type" value="individual_tests"
                            {{ request('report_type', 'individual_tests') === 'individual_tests' ? 'checked' : '' }}
                            class="rounded text-emerald-600">
                        Individual Tests
                    </label>
                    <label class="flex items-center gap-2 text-xs cursor-pointer">
                        <input type="radio" name="report_type" value="subject_average"
                            {{ request('report_type') === 'subject_average' ? 'checked' : '' }}
                            class="rounded text-emerald-600">
                        Average per Subject
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-medium rounded-lg shadow-xs transition">
                    Apply Filter
                </button>
            </div>

            <div class="flex flex-col justify-center gap-2">
                <a href="{{ route('students.history.report', $student) }}?{{ http_build_query(array_filter([
                    'subject_id' => request('subject_id'),
                    'date_from' => request('date_from'),
                    'date_to' => request('date_to'),
                    'report_type' => request('report_type', 'individual_tests'),
                ])) }}" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium text-center rounded-lg shadow-xs transition">
                    Generate Report
                </a>
                @if(request('subject_id') || request('date_from') || request('date_to'))
                    <a href="{{ route('students.history', $student) }}" class="w-full py-2 border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-medium text-center rounded-lg">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if($totalTests > 0 && auth()->user()->isAdmin())
        <form action="{{ route('students.clear-history', $student) }}" method="POST" class="flex justify-end" onsubmit="return confirm('Are you sure you want to delete all test history for this student? This action cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 text-sm font-medium text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 hover:text-rose-900 rounded-lg shadow-xs transition">
                Clear Test History
            </button>
        </form>
    @endif

    <!-- Test History Chronological Table (Newest First) -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-bold text-slate-900">Recorded Tests History</h3>
            <span class="text-xs text-slate-500">Showing tests in chronological order (newest first)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-700 font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Test Date</th>
                        <th class="px-4 py-3 text-left">Class at Test</th>
                        <th class="px-4 py-3 text-left">Subject</th>
                        <th class="px-4 py-3 text-left">Test Title</th>
                        <th class="px-4 py-3 text-center">Obtained</th>
                        <th class="px-4 py-3 text-center">Max Marks</th>
                        <th class="px-4 py-3 text-center">Percentage</th>
                        <th class="px-4 py-3 text-right">Report</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($results as $result)
                        @php
                            $test = $result->test;
                            $percentage = $test->max_marks > 0 ? round(($result->obtained_marks / $test->max_marks) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5 whitespace-nowrap font-medium text-slate-900">
                                {{ $test->test_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800">
                                    {{ $test->classSectionSubject->classSection->full_name }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap font-bold text-slate-800">
                                {{ $test->classSectionSubject->subject->name }}
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">
                                {{ $test->title }}
                            </td>
                            <td class="px-4 py-3.5 text-center font-bold text-slate-900">
                                {{ $result->obtained_marks }}
                            </td>
                            <td class="px-4 py-3.5 text-center text-slate-600">
                                {{ $test->max_marks }}
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $percentage >= 80 ? 'bg-emerald-100 text-emerald-800' : ($percentage >= 50 ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $percentage }}%
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-2 text-xs">
                                <a href="{{ route('reports.test', $test) }}" class="text-emerald-700 hover:text-emerald-900 font-semibold">
                                    View Report
                                </a>
                                <a href="{{ route('reports.test.pdf', $test) }}" class="text-slate-500 hover:text-slate-800 font-medium">
                                    PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-base font-semibold text-slate-700">No test records found</p>
                                <p class="text-xs text-slate-400 mt-1">This student does not have any recorded test marks matching the selected criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($results->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                {{ $results->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
