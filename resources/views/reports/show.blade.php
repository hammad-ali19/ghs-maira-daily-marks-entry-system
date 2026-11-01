@extends('layouts.app')

@section('title', 'Official Test Report: ' . $test->title)
@section('subtitle', 'Print-ready official school academic test performance sheet')

@section('actions')
    <button onclick="window.print()" class="no-print inline-flex items-center px-4 py-2 bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold rounded-lg shadow-sm transition">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        Print Report
    </button>
    <a href="{{ route('reports.test.pdf', $test) }}" class="no-print inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 text-xs font-bold rounded-lg border border-emerald-300 transition">
        Download PDF
    </a>
    <a href="{{ route('reports.test.csv', $test) }}" class="no-print inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-bold rounded-lg border border-slate-300 transition">
        Download CSV
    </a>
    <a href="{{ route('tests.show', $test) }}" class="no-print text-xs font-medium text-slate-600 hover:text-slate-900">
        &larr; Back to Test
    </a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl border border-slate-200 shadow-md p-8 print:p-0 print:border-none print:shadow-none">
    <!-- Official School Header -->
    <div class="text-center pb-6 border-b-2 border-emerald-900">
        <div class="text-xs uppercase font-extrabold tracking-widest text-emerald-800 mb-1">
            Government of the Punjab &bull; School Education Department
        </div>
        <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight">
            {{ config('app.name', 'Government High School Maira') }}
        </h1>
        <p class="text-sm font-semibold text-slate-700 mt-0.5">Daily Academic Test Assessment Report</p>
    </div>

    <!-- Test Meta Details Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-3 gap-x-6 py-5 text-sm border-b border-slate-200">
        <div>
            <span class="text-xs text-slate-500 uppercase font-bold block">Class & Section</span>
            <span class="font-extrabold text-slate-900 text-base">{{ $test->classSectionSubject->classSection->full_name }}</span>
        </div>
        <div>
            <span class="text-xs text-slate-500 uppercase font-bold block">Subject</span>
            <span class="font-extrabold text-slate-900 text-base">{{ $test->classSectionSubject->subject->name }}</span>
        </div>
        <div>
            <span class="text-xs text-slate-500 uppercase font-bold block">Test Date</span>
            <span class="font-bold text-slate-900 text-base">{{ $test->test_date->format('d M Y') }}</span>
        </div>
        <div>
            <span class="text-xs text-slate-500 uppercase font-bold block">Test Title</span>
            <span class="font-medium text-slate-800">{{ $test->title }}</span>
        </div>
        <div>
            <span class="text-xs text-slate-500 uppercase font-bold block">Maximum Marks</span>
            <span class="font-extrabold text-slate-900 text-base">{{ $test->max_marks }}</span>
        </div>
        <div>
            <span class="text-xs text-slate-500 uppercase font-bold block">Conducted By:</span>
            <span class="font-medium text-slate-800">{{ $test->creator->name }}</span>
        </div>
    </div>

    <!-- Student Marks Table -->
    <div class="py-6 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-300 text-sm border border-slate-300">
            <thead class="bg-slate-100 text-slate-900 font-bold text-xs">
                <tr>
                    <th class="px-3 py-2 text-left border border-slate-300 w-12">Sr.</th>
                    <th class="px-4 py-2 text-left border border-slate-300">Student Name</th>
                    <th class="px-4 py-2 text-left border border-slate-300">B-Form / Admission No.</th>
                    <th class="px-4 py-2 text-center border border-slate-300 w-28">Obtained Marks</th>
                    <th class="px-4 py-2 text-center border border-slate-300 w-24">Max Marks</th>
                    <th class="px-4 py-2 text-center border border-slate-300 w-24">Percentage</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($results as $result)
                    @php $pct = $result->percentage; @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-2 text-slate-500 font-mono text-xs border border-slate-300">{{ $loop->iteration }}</td>
                        <td class="px-4 py-2 font-bold text-slate-900 border border-slate-300">{{ $result->student->name }}</td>
                        <td class="px-4 py-2 font-mono text-xs text-slate-600 border border-slate-300">{{ $result->student->b_form_number }}</td>
                        <td class="px-4 py-2 text-center font-bold text-slate-900 border border-slate-300">{{ $result->obtained_marks }}</td>
                        <td class="px-4 py-2 text-center text-slate-600 border border-slate-300">{{ $test->max_marks }}</td>
                        <td class="px-4 py-2 text-center font-bold border border-slate-300 {{ $pct >= 50 ? 'text-emerald-800' : 'text-rose-700' }}">
                            {{ $pct }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Summary Performance Statistics Box -->
    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center my-4">
        <div>
            <span class="text-xs text-slate-500 font-bold uppercase block">Total Students</span>
            <span class="text-xl font-extrabold text-slate-900">{{ $results->count() }}</span>
        </div>
        <div>
            <span class="text-xs text-slate-500 font-bold uppercase block">Class Average</span>
            <span class="text-xl font-extrabold text-emerald-800">{{ $averageMarks }} / {{ $test->max_marks }}</span>
        </div>
        <div>
            <span class="text-xs text-slate-500 font-bold uppercase block">Highest Marks</span>
            <span class="text-xl font-extrabold text-slate-900">{{ $highestMarks }}</span>
        </div>
        <div>
            <span class="text-xs text-slate-500 font-bold uppercase block">Lowest Marks</span>
            <span class="text-xl font-extrabold text-slate-900">{{ $lowestMarks }}</span>
        </div>
    </div>

    <!-- Official Signature Footers -->
    <div class="pt-16 grid grid-cols-3 gap-6 text-center text-xs font-bold text-slate-700">
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
</div>
@endsection
