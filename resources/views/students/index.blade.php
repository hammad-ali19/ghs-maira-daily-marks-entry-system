@extends('layouts.app')

@section('title', 'Students Directory')
@section('subtitle', 'School-wide student records, enrollments and test histories')

@section('actions')
    <a href="{{ route('students.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-800 hover:bg-emerald-900 text-white text-sm font-medium rounded-lg shadow-sm transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3-3h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
        </svg>
        Enroll New Student
    </a>
@endsection

@section('content')
<div class="space-y-4">
    <!-- Filters and Search Bar -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('students.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by student name or B-Form..."
                    class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600">
            </div>

            <div>
                <select name="class_section_id" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600">
                    <option value="">-- All Classes & Sections --</option>
                    @foreach($sections as $sec)
                        <option value="{{ $sec->id }}" {{ request('class_section_id') == $sec->id ? 'selected' : '' }}>
                            {{ $sec->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium rounded-lg shadow-xs transition">
                    Filter
                </button>
                @if(request('search') || request('class_section_id') || request('status') !== null)
                    <a href="{{ route('students.index') }}" class="px-3 py-2 border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm font-medium rounded-lg">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Students Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-700 font-semibold">
                    <tr>
                        {{-- <th class="px-4 py-3 text-left">Photo</th> --}}
                        <th class="px-4 py-3 text-left">Student Name</th>
                        <th class="px-4 py-3 text-left">B-Form Number (Unique)</th>
                        <th class="px-4 py-3 text-left">Current Class</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">History</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                        <tr class="hover:bg-slate-50/70 transition">
                            {{-- <td class="px-4 py-3 whitespace-nowrap">
                                @if($student->photo_path)
                                    <img src="{{ asset('storage/' . $student->photo_path) }}" alt="{{ $student->name }}" class="w-10 h-10 rounded-full object-cover border border-slate-200 shadow-xs">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-xs">
                                        {{ substr($student->name, 0, 1) }}
                                    </div>
                                @endif
                            </td> --}}
                            <td class="px-4 py-3 whitespace-nowrap font-bold text-slate-900">
                                <a href="{{ route('students.history', $student) }}" class="hover:text-emerald-700">
                                    {{ $student->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap font-mono text-xs text-slate-600">
                                {{ $student->b_form_number }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($student->currentEnrollment)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        {{ $student->currentEnrollment->classSection->full_name }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Not Enrolled</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                @if($student->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right space-x-2 text-xs">
                                <a href="{{ route('students.history', $student) }}" class="text-emerald-700 hover:text-emerald-900 font-bold bg-emerald-50 px-2 py-1 rounded border border-emerald-200">
                                    Test History
                                </a>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right space-x-2 text-xs">
                                
                                @php
                                    $currSec = $student->currentEnrollment?->classSection;
                                    $canManage = auth()->user()->isAdmin() || (auth()->user()->isTeacher() && $currSec && $currSec->incharge_id === auth()->id());
                                @endphp
                                @if($canManage)
                                    <a href="{{ route('students.edit', $student) }}" class="text-slate-600 hover:text-slate-900 font-medium">
                                        Edit
                                    </a>
                                    <form action="{{ route('students.destroy', $student) }}" method="POST" class="inline" onsubmit="return confirm('Delete student?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-900 font-medium cursor-pointer">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                No students found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
