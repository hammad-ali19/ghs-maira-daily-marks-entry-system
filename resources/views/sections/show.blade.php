@extends('layouts.app')

@section('title', 'Class: ' . $section->full_name)
@section('subtitle', 'Students, subjects, teacher assignments and class incharge details')

@section('actions')
@if($isAuthorizedIncharge)
<a href="{{ route('students.create', ['class_section_id' => $section->id]) }}"
    class="inline-flex items-center px-3.5 py-1.5 bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-semibold rounded-lg shadow-sm transition">
    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M18 9v3m0 0v3m0-3h3m-3-3h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
    </svg>
    Enroll Student
</a>
<a href="{{ route('tests.create', ['class_section_id' => $section->id]) }}"
    class="inline-flex items-center px-3.5 py-1.5 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 text-xs font-semibold rounded-lg border border-emerald-200 transition">
    Record Daily Marks
</a>
@endif
<a href="{{ route('classes.show', $section->schoolClass) }}"
    class="text-xs font-medium text-slate-600 hover:text-slate-900">
    &larr; Class Overview
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header Banner: Incharge Information & Section Status -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Class Section</span>
                <h2 class="text-2xl font-bold text-slate-900">{{ $section->full_name }}</h2>
                <div class="flex items-center space-x-3 mt-1 text-sm text-slate-600">
                    <span>Grade: <strong>{{ $section->schoolClass->name }}</strong></span>
                    <span>&bull;</span>
                    <span>Section: <strong>{{ $section->name }}</strong></span>
                    <span>&bull;</span>
                    <span>Students: <strong>{{ $section->currentEnrollments->count() }}</strong></span>
                    <span>&bull;</span>
                    <span>Subjects: <strong>{{ $section->classSectionSubjects->count() }}</strong></span>
                </div>
            </div>

            <!-- Incharge Box & Admin Assignment -->
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 min-w-70">
                <span class="block text-xs font-semibold text-slate-500 uppercase">Designated Class Incharge</span>
                <div class="mt-1 flex items-center justify-between">
                    <span class="font-bold text-slate-900 text-sm">
                        {{ $section->incharge ? $section->incharge->name : 'No Incharge Assigned' }}
                    </span>
                    @if($section->incharge)
                    <span class="text-xs text-slate-500">({{ $section->incharge->email }})</span>
                    @endif
                </div>

                @if(auth()->user()->isAdmin())
                <form id="incharge-form" action="{{ route('sections.update', $section) }}" method="POST"
                    class="mt-3 pt-3 border-t border-slate-200 flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="name" value="{{ $section->name }}">
                    <input type="hidden" name="is_active" value="{{ $section->is_active ? 1 : 0 }}">

                    <select id="incharge_id" name="incharge_id"
                        class="text-xs py-1 px-2 rounded border border-slate-300 flex-1 focus:ring-emerald-600">
                        <option value="">-- Change Incharge --</option>
                        @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ $section->incharge_id == $t->id ? 'selected' : '' }}
                            data-name="{{ $t->name }}">
                            {{ $t->name }}
                        </option>
                        @endforeach
                    </select>
                    <button type="submit"
                        class="px-2.5 py-1 bg-emerald-800 text-white rounded text-xs font-medium hover:bg-emerald-900">
                        Assign
                    </button>
                </form>
                <script>
                    document.getElementById('incharge-form').addEventListener('submit', function (e) {
                        const select = document.getElementById('incharge_id');
                        const selected = select.options[select.selectedIndex];
                        if (selected.value && selected.value !== '{{ $section->incharge_id }}') {
                            const teacherName = selected.dataset.name;
                            const inchargeAssignments = @json($inchargeAssignments);
                            if (inchargeAssignments[selected.value]) {
                                if (!confirm('Teacher ' + teacherName + ' is already the incharge of another class proceeding will remove them as incharge of previous class')) {
                                    e.preventDefault();
                                }
                            }
                        }
                    });
                </script>
                @endif
            </div>
        </div>
    </div>

    <!-- Two Column Layout: Left Column = Subjects & Teachers, Right Column = Enrolled Students -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 w-full min-w-0">
        <!-- Subjects & Teachers (5 cols) -->
        @if(auth()->user()->isAdmin())
        <div class="w-full min-w-0 lg:col-span-5 space-y-5">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 min-w-0 w-full">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Class Subjects</h3>
                        <p class="text-xs text-slate-500">Subjects and assigned teachers for {{ $section->full_name }}
                        </p>
                    </div>
                </div>

                <!-- Add Subject Form (Admin or Incharge) -->
                @if($isAuthorizedIncharge)
                <form action="{{ route('sections.subjects.assign', $section) }}" method="POST"
                    class="mb-5 p-3 bg-emerald-50/60 rounded-lg border border-emerald-200 flex items-center gap-2">
                    @csrf
                    <div class="flex-1">
                        <select name="subject_id" required
                            class="w-full text-xs py-1.5 px-2 rounded border border-slate-300 bg-white focus:ring-emerald-600">
                            <option value="">-- Add Subject from Catalogue --</option>
                            @foreach($availableSubjects as $as)
                            <option value="{{ $as->id }}">{{ $as->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                        class="px-3 py-1.5 bg-emerald-800 text-white rounded text-xs font-medium hover:bg-emerald-900 shrink-0">
                        + Add Subject
                    </button>
                </form>
                @endif

                <!-- Subjects List -->
                <div class="space-y-3">
                    @forelse($section->classSectionSubjects as $css)
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <div class="flex flex-wrap items-center justify-between gap-2 min-w-0">
                            <span class="font-bold text-emerald-950 text-sm min-w-0 flex-1 wrap-break-word">{{
                                $css->subject->name }}</span>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('tests.create', ['class_section_id' => $section->id, 'subject_id' => $css->id]) }}"
                                    class="text-[11px] font-semibold text-emerald-700 hover:text-emerald-900 bg-emerald-100/70 px-2 py-0.5 rounded">
                                    Test Marks
                                </a>
                                @if($isAuthorizedIncharge)
                                <form
                                    action="{{ route('sections.subjects.remove', ['section' => $section, 'classSectionSubject' => $css]) }}"
                                    method="POST" class="inline"
                                    onsubmit="return confirm('Remove {{ $css->subject->name }} from {{ $section->full_name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs p-1"
                                        title="Remove Subject">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>

                        <!-- Assigned Teachers for this Subject -->
                        <div class="mt-2 pt-2 border-t border-slate-200/60">
                            <span class="text-[11px] text-slate-500 font-semibold block mb-1">Assigned Teachers:</span>
                            <div class="flex flex-wrap gap-1.5 mb-2">
                                @forelse($css->teacherAssignments as $ta)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-white border border-slate-300 text-slate-800">
                                    {{ $ta->teacher->name }}
                                    @if($isAuthorizedIncharge)
                                    <form action="{{ route('assignments.destroy', $ta) }}" method="POST"
                                        class="inline ml-1"
                                        onsubmit="return confirm('Unassign {{ $ta->teacher->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-slate-400 hover:text-rose-600 font-bold ml-1">&times;</button>
                                    </form>
                                    @endif
                                </span>
                                @empty
                                <span class="text-xs text-slate-400 italic">No teacher assigned yet (incharge or admin
                                    can record)</span>
                                @endforelse
                            </div>

                            <!-- Assign Teacher Form -->
                            @if($isAuthorizedIncharge)
                            <form action="{{ route('assignments.store') }}" method="POST"
                                class="flex items-center gap-2 mt-2">
                                @csrf
                                <input type="hidden" name="class_section_subject_id" value="{{ $css->id }}">
                                <select name="user_id" required
                                    class="text-[11px] py-1 px-2 rounded border border-slate-300 flex-1 focus:ring-emerald-600 bg-white">
                                    <option value="">-- Assign Teacher to {{ $css->subject->name }} --</option>
                                    @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit"
                                    class="px-2 py-1 bg-slate-700 hover:bg-slate-800 text-white rounded text-[11px] font-medium">
                                    Assign
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6 text-xs text-slate-500">
                        No subjects assigned yet to this class. Use the form above to add subjects.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        @elseif($isAuthorizedIncharge)
        <div class="w-full min-w-0 lg:col-span-4 space-y-2">

            @forelse($section->classSectionSubjects as $css)

            <div
                class="flex flex-row justify-between items-center gap-2 p-3 bg-white rounded-lg border border-slate-200 min-w-0">

                <!-- Subject -->
                <div class="min-w-0">
                    <span class="font-bold text-emerald-950 text-sm wrap-break-word">
                        {{ $css->subject->name }}
                    </span>
                </div>

                <!-- Assigned Teachers -->
                <div class="min-w-0 flex flex-wrap gap-1">

                    @forelse($css->teacherAssignments as $ta)
                    <span
                        class="inline-block max-w-full px-2 py-1 rounded text-xs bg-white border border-slate-300 text-slate-800 wrap-break-word">
                        {{ $ta->teacher->name }}
                    </span>
                    @empty
                    <span class="text-xs text-slate-400 italic">
                        No teacher assigned yet.
                    </span>
                    @endforelse

                </div>

            </div>

            @empty
            <div class="text-center py-6 text-xs text-slate-500">
                No subjects assigned yet to this class.
            </div>
            @endforelse

        </div>
        @endif
        <!-- Enrolled Students (7 cols) -->
        <div class="w-full min-w-0 {{ auth()->user()->isAdmin() ? 'lg:col-span-7' : 'lg:col-span-8' }}">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3 min-w-0">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Enrolled Students</h3>
                        <p class="text-xs text-slate-500">Total: {{ $section->currentEnrollments->count() }} active
                            students</p>
                    </div>
                    @if($isAuthorizedIncharge)
                    <a href="{{ route('students.create', ['class_section_id' => $section->id]) }}"
                        class="px-3 py-1 bg-emerald-800 hover:bg-emerald-900 text-white rounded text-xs font-semibold shadow-xs">
                        + Add Student
                    </a>
                    @endif
                </div>

                <div class="w-full max-w-full min-w-0 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-700 font-semibold text-xs">
                            <tr>
                                {{-- <th class="px-4 py-2.5 text-left">Photo</th> --}}
                                <th class="px-4 py-2.5 text-left">Student Name</th>
                                <th class="px-4 py-2.5 text-left">B-Form Number</th>
                                <th class="px-4 py-2.5 text-center">Status</th>
                                <th class="px-4 py-2.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($section->currentEnrollments as $enrollment)
                            @php $student = $enrollment->student; @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                {{-- <td class="px-4 py-2.5 whitespace-nowrap">
                                    @if($student->photo_path)
                                    <img src="{{ asset('storage/' . $student->photo_path) }}" alt="{{ $student->name }}"
                                        class="w-9 h-9 rounded-full object-cover border border-slate-200">
                                    @else
                                    <div
                                        class="w-9 h-9 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-xs">
                                        {{ substr($student->name, 0, 1) }}
                                    </div>
                                    @endif
                                </td> --}}
                                <td class="px-4 py-2.5 whitespace-nowrap font-medium text-slate-900">
                                    <a href="{{ route('students.history', $student) }}" class="hover:text-emerald-700">
                                        {{ $student->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap font-mono text-xs text-slate-600">
                                    {{ $student->b_form_number }}
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap text-center">
                                    @if($student->is_active)
                                    <span
                                        class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">Active</span>
                                    @else
                                    <span
                                        class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-600">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap text-right space-x-2 text-xs">
                                    <a href="{{ route('students.history', $student) }}"
                                        class="text-emerald-700 hover:text-emerald-900 font-semibold"
                                        title="View Test Marks History">
                                        History
                                    </a>
                                    @if($isAuthorizedIncharge)
                                    <a href="{{ route('students.edit', $student) }}"
                                        class="text-slate-600 hover:text-slate-900">
                                        Edit
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500 text-xs">
                                    No students enrolled in this section yet. Click "+ Add Student" to register
                                    students.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection