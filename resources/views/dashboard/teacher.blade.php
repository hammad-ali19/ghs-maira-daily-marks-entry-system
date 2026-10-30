@extends('layouts.app')

@section('title', 'Teacher Dashboard')
@section('subtitle', 'Welcome back, ' . auth()->user()->name . '. View your classes, teaching assignments and tests.')

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
    <!-- Quick Overview Banners -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Incharge Classes Box -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-slate-900 flex items-center">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 mr-2"></span>
                    Class Incharge Responsibilities
                </h3>
                <span class="text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded font-medium">
                    {{ $inchargeSections->count() }} {{ Str::plural('Class', $inchargeSections->count()) }}
                </span>
            </div>

            @if($inchargeSections->isEmpty())
                <p class="text-sm text-slate-500 py-3">You are currently not designated as an incharge for any class section.</p>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($inchargeSections as $section)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-emerald-900 text-base">{{ $section->full_name }}</span>
                                <span class="text-xs text-slate-500 block">{{ $section->currentEnrollments->count() }} Enrolled Students</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('students.create', ['class_section_id' => $section->id]) }}" class="px-2.5 py-1 text-xs bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded font-medium border border-emerald-200">
                                    + Add Student
                                </a>
                                <a href="{{ route('sections.show', $section) }}" class="px-2.5 py-1 text-xs bg-slate-100 text-slate-700 hover:bg-slate-200 rounded font-medium">
                                    Manage Class
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Teaching Assignments Box -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-slate-900 flex items-center">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600 mr-2"></span>
                    Your Teaching Subjects
                </h3>
                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-medium">
                    {{ $teachingAssignments->count() }} {{ Str::plural('Subject', $teachingAssignments->count()) }}
                </span>
            </div>

            @if($teachingAssignments->isEmpty())
                <p class="text-sm text-slate-500 py-3">No specific subject teaching assignments recorded yet. The school administrator or class incharge can assign subjects to you.</p>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($teachingAssignments as $assignment)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <span class="font-semibold text-slate-900">{{ $assignment->classSectionSubject->subject->name }}</span>
                                <span class="text-xs text-slate-500 block">Class: {{ $assignment->classSectionSubject->classSection->full_name }}</span>
                            </div>
                            <a href="{{ route('tests.create', [
                                'class_section_id' => $assignment->classSectionSubject->class_section_id,
                                'subject_id' => $assignment->classSectionSubject->id
                            ]) }}" class="px-3 py-1 text-xs bg-emerald-800 hover:bg-emerald-900 text-white rounded font-medium">
                                Enter Marks
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Tests Conducted / Relevant -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900">Recent Tests & Marks</h3>
                <p class="text-xs text-slate-500">Tests you have recorded or can access</p>
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
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('tests.show', $test) }}" class="text-emerald-700 hover:text-emerald-900 font-medium text-xs">View Marks</a>
                                <a href="{{ route('reports.test.pdf', $test) }}" class="text-slate-500 hover:text-slate-800 font-medium text-xs">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                No test marks recorded yet. Click "Record Marks" to enter marks.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
