@extends('layouts.app')

@section('title', 'Student History Report: ' . $student->name)
@section('subtitle', 'Print-ready academic test performance report for ' . $student->name)

@section('actions')
    <a href="{{ route('students.history', $student) }}" class="no-print inline-flex items-center px-3 py-2 text-xs font-medium text-slate-600 hover:text-slate-900 border border-slate-300 hover:bg-slate-50 rounded-lg transition">
        &larr; Back to History
    </a>
    <button onclick="window.print()" class="no-print inline-flex items-center px-4 py-2 bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold rounded-lg shadow-sm transition">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        Print Report
    </button>
    <a href="{{ route('students.history.report.pdf', $student) }}?{{ http_build_query(array_filter([
        'subject_id' => request('subject_id'),
        'date_from' => request('date_from'),
        'date_to' => request('date_to'),
        'report_type' => request('report_type', 'individual_tests'),
    ])) }}" class="no-print inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 text-xs font-bold rounded-lg border border-emerald-300 transition">
        Download PDF
    </a>
@endsection

@section('content')
<div class="max-w-5xl mx-auto bg-white rounded-xl border border-slate-200 shadow-md p-8 print:p-0 print:border-none print:shadow-none print:bg-transparent">
    <!-- Official School Header -->
    <div class="text-center pb-6 border-b-2 border-emerald-900 print:border-b-0">
        <div class="text-xs uppercase font-extrabold tracking-widest text-emerald-800 mb-1">
            Government of the Punjab &bull; School Education Department
        </div>
        <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight">
            {{ config('app.name', 'Government High School Maira') }}
        </h1>
        <p class="text-sm font-semibold text-slate-700 mt-0.5">Student Academic Test History Report</p>
    </div>

    <!-- Student Profile -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-y-4 gap-x-6 py-5 text-sm border-b border-slate-200 print:border-b-0">
        <div>
            <span class="text-xs text-slate-500 uppercase font-bold block">Student Name</span>
            <span class="font-extrabold text-slate-900 text-base">{{ $student->name }}</span>
        </div>
        <div>
            <span class="text-xs text-slate-500 uppercase font-bold block">B-Form / CNIC</span>
            <span class="font-mono font-bold text-slate-900 text-base">{{ $student->b_form_number }}</span>
        </div>
        <div>
            <span class="text-xs text-slate-500 uppercase font-bold block">Current Class</span>
            <span class="font-bold text-slate-900 text-base">{{ $student->currentEnrollment?->classSection->full_name ?? 'Not Enrolled' }}</span>
        </div>
    </div>

    <!-- Report Criteria -->
    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-6 text-sm print:bg-transparent print:border-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <span class="text-xs text-slate-500 font-bold uppercase block">Report Criteria</span>
                <span class="text-slate-800 font-medium">
                    @if($reportType === 'subject_average')
                        Average per Subject
                    @else
                        Individual Tests
                    @endif
                </span>
            </div>
            @if($dateFrom || $dateTo)
                <div class="flex items-center gap-1 text-slate-600">
                    <span class="text-xs font-semibold uppercase">Date Range:</span>
                    <span>{{ $dateFrom ?? 'Any' }} to {{ $dateTo ?? 'Any' }}</span>
                </div>
            @endif
            @if($subjectId)
                <div class="text-slate-600">
                    <span class="text-xs font-semibold uppercase">Subject:</span>
                    <span>{{ optional($subjects->first(fn ($s) => $s->id == $subjectId))->name ?? $subjectId }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Overall Summary -->
    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center mb-6 print:bg-transparent print:border-0">
        <div>
            <span class="text-xs text-slate-500 font-bold uppercase block">Total Tests</span>
            <span class="text-xl font-extrabold text-slate-900">{{ $totalTests }}</span>
        </div>
        <div>
            <span class="text-xs text-slate-500 font-bold uppercase block">Tests Appeared</span>
            <span class="text-xl font-extrabold text-emerald-800">{{ $testsAppeared }}</span>
        </div>
        <div>
            <span class="text-xs text-slate-500 font-bold uppercase block">Tests Absent</span>
            <span class="text-xl font-extrabold text-rose-700">{{ $totalAbsents }}</span>
        </div>
        <div>
            <span class="text-xs text-slate-500 font-bold uppercase block">Overall Average</span>
            <span class="text-xl font-extrabold {{ $overallAverage >= 50 ? 'text-emerald-700' : 'text-amber-600' }}">{{ $overallAverage }}%</span>
        </div>
    </div>

    @if($totalTests === 0)
        <div class="px-4 py-16 text-center text-slate-500">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-base font-semibold text-slate-700">No test records found</p>
            <p class="text-xs text-slate-400 mt-1">This student does not have any recorded test marks matching the selected criteria.</p>
        </div>
    @else
        @if($reportType === 'subject_average')
            <!-- Subject Average Summary -->
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full divide-y divide-slate-300 text-sm border border-slate-300">
                    <thead class="bg-slate-100 text-slate-900 font-bold text-xs">
                        <tr>
                            <th class="px-3 py-2 text-left border border-slate-300">Subject</th>
                            <th class="px-3 py-2 text-center border border-slate-300">Total Tests</th>
                            <th class="px-3 py-2 text-center border border-slate-300">Appeared</th>
                            <th class="px-3 py-2 text-center border border-slate-300">Absent</th>
                            <th class="px-3 py-2 text-center border border-slate-300">Avg Percentage</th>
                            <th class="px-3 py-2 text-center border border-slate-300">Avg Marks</th>
                            <th class="px-3 py-2 text-center border border-slate-300">Total Obtained</th>
                            <th class="px-3 py-2 text-center border border-slate-300">Total Max</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($subjectSummary as $summary)
                            @php
                                $sub = $summary['subject'];
                                $avgPct = $summary['average_percentage'];
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 font-bold text-slate-900 border border-slate-300">{{ $sub->name }}</td>
                                <td class="px-3 py-2 text-center text-slate-600 border border-slate-300">{{ $summary['count'] }}</td>
                                <td class="px-3 py-2 text-center text-emerald-800 font-semibold border border-slate-300">{{ $summary['appeared_count'] }}</td>
                                <td class="px-3 py-2 text-center border border-slate-300 {{ $summary['absent_count'] > 0 ? 'text-rose-700 font-bold' : 'text-slate-600' }}">{{ $summary['absent_count'] }}</td>
                                <td class="px-3 py-2 text-center font-bold border border-slate-300 {{ $avgPct >= 50 ? 'text-emerald-800' : 'text-rose-700' }}">{{ $avgPct }}%</td>
                                <td class="px-3 py-2 text-center text-slate-600 border border-slate-300">{{ $summary['average_marks'] }}</td>
                                <td class="px-3 py-2 text-center text-slate-600 border border-slate-300">{{ $summary['total_obtained'] }}</td>
                                <td class="px-3 py-2 text-center text-slate-600 border border-slate-300">{{ $summary['total_max'] }}</td>
                            </tr>
                            <!-- Individual tests under this subject -->
                            <tr>
                                <td colspan="8" class="p-0 border border-slate-300">
                                    <div class="bg-slate-50 border-b border-slate-200 p-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-semibold text-slate-500 uppercase">Test Details</span>
                                        </div>
                                    </div>
                                    <table class="min-w-full text-xs">
                                        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase text-[10px]">
                                            <tr>
                                                <th class="px-3 py-1.5 text-left border-b border-slate-200">Date</th>
                                                <th class="px-3 py-1.5 text-left border-b border-slate-200">Class & Section</th>
                                                <th class="px-3 py-1.5 text-left border-b border-slate-200">Test Title</th>
                                                <th class="px-3 py-1.5 text-center border-b border-slate-200">Obtained</th>
                                                <th class="px-3 py-1.5 text-center border-b border-slate-200">Max</th>
                                                <th class="px-3 py-1.5 text-center border-b border-slate-200">Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach($summary['results'] as $result)
                                                @php
                                                    $test = $result->test;
                                                    $pct = $result->percentage;
                                                @endphp
                                                <tr class="hover:bg-slate-50/50 {{ $result->is_absent ? 'bg-rose-50/40' : '' }}">
                                                    <td class="px-3 py-1.5 text-slate-600 whitespace-nowrap border-b border-slate-100">{{ $test->test_date->format('d M Y') }}</td>
                                                    <td class="px-3 py-1.5 text-slate-600 whitespace-nowrap border-b border-slate-100 text-xs">{{ $test->classSectionSubject->classSection->full_name }}</td>
                                                    <td class="px-3 py-1.5 text-slate-600 border-b border-slate-100">{{ $test->title }}</td>
                                                    <td class="px-3 py-1.5 text-center font-bold border-b border-slate-100 {{ $result->is_absent ? 'text-rose-700' : 'text-slate-900' }}">
                                                        @if($result->is_absent)
                                                            Absent
                                                        @else
                                                            {{ $result->obtained_marks }}
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-1.5 text-center text-slate-600 border-b border-slate-100">{{ $test->max_marks }}</td>
                                                    <td class="px-3 py-1.5 text-center text-slate-600 border-b border-slate-100">
                                                        @if($result->is_absent)
                                                            -
                                                        @else
                                                            {{ $pct }}%
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <!-- Individual Tests Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-300 text-sm border border-slate-300">
                    <thead class="bg-slate-100 text-slate-900 font-bold text-xs">
                        <tr>
                            <th class="px-3 py-2 text-left border border-slate-300">Test Date</th>
                            <th class="px-3 py-2 text-left border border-slate-300">Class & Section</th>
                            <th class="px-3 py-2 text-left border border-slate-300">Subject</th>
                            <th class="px-3 py-2 text-left border border-slate-300">Test Title</th>
                            <th class="px-3 py-2 text-center border border-slate-300">Obtained</th>
                            <th class="px-3 py-2 text-center border border-slate-300">Max Marks</th>
                            <th class="px-3 py-2 text-center border border-slate-300">Percentage</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($results as $result)
                            @php
                                $test = $result->test;
                                $pct = $result->percentage;
                            @endphp
                            <tr class="hover:bg-slate-50 {{ $result->is_absent ? 'bg-rose-50/40' : '' }}">
                                <td class="px-3 py-2 whitespace-nowrap font-medium text-slate-900 border border-slate-300">{{ $test->test_date->format('d M Y') }}</td>
                                <td class="px-3 py-2 text-slate-600 border border-slate-300">{{ $test->classSectionSubject->classSection->full_name }}</td>
                                <td class="px-3 py-2 font-bold text-slate-800 border border-slate-300">{{ $test->classSectionSubject->subject->name }}</td>
                                <td class="px-3 py-2 text-slate-600 border border-slate-300">{{ $test->title }}</td>
                                <td class="px-3 py-2 text-center font-bold border border-slate-300 {{ $result->is_absent ? 'text-rose-700' : 'text-slate-900' }}">
                                    @if($result->is_absent)
                                        Absent
                                    @else
                                        {{ $result->obtained_marks }}
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-center text-slate-600 border border-slate-300">{{ $test->max_marks }}</td>
                                <td class="px-3 py-2 text-center whitespace-nowrap border border-slate-300">
                                    @if($result->is_absent)
                                        <span class="text-xs text-slate-400 font-medium">-</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $pct >= 80 ? 'bg-emerald-100 text-emerald-800' : ($pct >= 50 ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800') }}">
                                            {{ $pct }}%
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Official Signature Footers -->
        <div class="mt-12 grid grid-cols-3 gap-6 text-center text-xs font-bold text-slate-700 print:mt-20">
            <div>
                <div class="border-t border-slate-400 pt-2 w-36 mx-auto"></div>
                <span>Subject Teacher</span>
            </div>
            <div>
                <div class="border-t border-slate-400 pt-2 w-36 mx-auto"></div>
                <span>Class Incharge</span>
            </div>
            <div>
                <div class="border-t border-slate-400 pt-2 w-36 mx-auto"></div>
                <span>Headmaster / Principal</span>
            </div>
        </div>
    @endif
</div>
@endsection
