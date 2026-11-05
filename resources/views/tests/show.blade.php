@extends('layouts.app')

@section('title', 'Test Record: ' . $test->title)
@section('subtitle', 'Detailed student marks breakdown and performance analytics')

@section('actions')
    @if($canEdit)
        <a href="{{ route('tests.edit', $test) }}" class="inline-flex items-center px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg shadow-xs transition">
            Edit Marks
        </a>
    @endif
    <a href="{{ route('reports.test', $test) }}" class="inline-flex items-center px-3.5 py-1.5 bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-semibold rounded-lg shadow-xs transition">
        Print / Preview Report
    </a>
    <a href="{{ route('reports.test.pdf', $test) }}" class="inline-flex items-center px-3.5 py-1.5 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 text-xs font-semibold rounded-lg border border-emerald-300 transition">
        Download PDF
    </a>
    <a href="{{ route('reports.test.csv', $test) }}" class="inline-flex items-center px-3.5 py-1.5 bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold rounded-lg border border-slate-300 transition">
        Export CSV
    </a>
    <a href="{{ route('tests.index') }}" class="text-xs font-medium text-slate-600 hover:text-slate-900">
        &larr; Back
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Test Metadata Card -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">
                        {{ $test->classSectionSubject->classSection->full_name }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-800">
                        {{ $test->classSectionSubject->subject->name }}
                    </span>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mt-2">{{ $test->title }}</h2>
                <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-slate-500 mt-1">
                    <span>Date Conducted: <strong>{{ $test->test_date->format('l, d F Y') }}</strong></span>
                    <span>&bull;</span>
                    <span>Maximum Marks: <strong>{{ $test->max_marks }}</strong></span>
                    <span>&bull;</span>
                    <span>Conducted By: <strong>{{ $test->creator->name }}</strong></span>
                </div>
            </div>

            <!-- Performance Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 bg-slate-50 border border-slate-200 rounded-xl p-3 px-4 text-center">
                <div>
                    <span class="block text-[11px] uppercase font-bold text-slate-400">Class Average</span>
                    <span class="text-xl font-extrabold text-emerald-700">{{ $averageMarks }} / {{ $test->max_marks }}</span>
                </div>
                <div class="border-l border-slate-200 sm:border-x">
                    <span class="block text-[11px] uppercase font-bold text-slate-400">Highest</span>
                    <span class="text-xl font-extrabold text-slate-800">{{ $highestMarks }}</span>
                </div>
                <div>
                    <span class="block text-[11px] uppercase font-bold text-slate-400">Lowest</span>
                    <span class="text-xl font-extrabold text-slate-800">{{ $lowestMarks }}</span>
                </div>
                <div class="border-l border-slate-200 sm:border-x">
                    <span class="block text-[11px] uppercase font-bold text-emerald-700">Present</span>
                    <span class="text-xl font-extrabold text-emerald-800">{{ $presentCount }}</span>
                </div>
                <div>
                    <span class="block text-[11px] uppercase font-bold text-rose-600">Absent</span>
                    <span class="text-xl font-extrabold text-rose-700">{{ $absentCount }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Results Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-bold text-slate-900">Student Results Register</h3>
            <span class="text-xs text-slate-500">Total: {{ $results->count() }} students ({{ $presentCount }} Present, {{ $absentCount }} Absent)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-700 font-semibold text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left w-12">Sr.#</th>
                        <th class="px-4 py-3 text-left">Photo</th>
                        <th class="px-4 py-3 text-left">Student Name</th>
                        <th class="px-4 py-3 text-left">B-Form Number</th>
                        <th class="px-4 py-3 text-center">Obtained Marks</th>
                        <th class="px-4 py-3 text-center">Maximum Marks</th>
                        <th class="px-4 py-3 text-center">Percentage</th>
                        <th class="px-4 py-3 text-right">Student History</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($results as $index => $result)
                        @php
                            $pct = $result->percentage;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition {{ $result->is_absent ? 'bg-rose-50/30' : '' }}">
                            <td class="px-4 py-3 text-slate-400 font-mono text-xs">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($result->student->photo_path)
                                    <img src="{{ asset('storage/' . $result->student->photo_path) }}" alt="{{ $result->student->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs">
                                        {{ substr($result->student->name, 0, 1) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap font-bold text-slate-900">
                                <a href="{{ route('students.history', $result->student) }}" class="hover:text-emerald-700">
                                    {{ $result->student->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap font-mono text-xs text-slate-600">
                                {{ $result->student->b_form_number }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center font-bold text-base text-slate-900">
                                @if($result->is_absent)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-800">
                                        Absent
                                    </span>
                                @else
                                    {{ $result->obtained_marks }}
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center text-slate-600">
                                {{ $test->max_marks }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                @if($result->is_absent)
                                    <span class="text-xs text-slate-400 font-medium">-</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $pct >= 80 ? 'bg-emerald-100 text-emerald-800' : ($pct >= 50 ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $pct }}%
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right text-xs">
                                <a href="{{ route('students.history', $result->student) }}" class="text-emerald-700 hover:text-emerald-900 font-semibold">
                                    Full History &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                No student marks recorded for this test.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
