@extends('layouts.app')

@section('title', 'Record Daily Test Marks')
@section('subtitle', 'Conduct a test and record student marks in a register format')

@section('actions')
    <a href="{{ route('tests.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
        &larr; Back to Tests Register
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Step 1: Select Class Section & Subject Filter Form -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-3">1. Select Target Class & Subject</h3>
        <form method="GET" action="{{ route('tests.create') }}" id="classSelectionForm" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="class_section_id" class="block text-xs font-semibold text-slate-700 mb-1">Class Section <span class="text-rose-500">*</span></label>
                <select name="class_section_id" id="class_section_id" onchange="document.getElementById('classSelectionForm').submit()"
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 text-sm">
                    <option value="">-- Choose Class Section --</option>
                    @foreach($sections as $sec)
                        <option value="{{ $sec->id }}" {{ $selectedSectionId == $sec->id ? 'selected' : '' }}>
                            {{ $sec->full_name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500 mt-1">Selecting a class loads its subjects and enrolled students.</p>
            </div>

            <div>
                <label for="subject_filter" class="block text-xs font-semibold text-slate-700 mb-1">Subject</label>
                @if($availableSubjects->isNotEmpty())
                    <select name="subject_id" id="subject_filter" onchange="document.getElementById('classSelectionForm').submit()"
                        class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 text-sm">
                        <option value="">-- Choose Subject --</option>
                        @foreach($availableSubjects as $as)
                            <option value="{{ $as->class_section_subject_id }}" {{ $selectedSubjectId == $as->class_section_subject_id ? 'selected' : '' }}>
                                {{ $as->name }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <div class="px-3.5 py-2.5 rounded-lg border border-dashed border-slate-300 text-xs text-slate-400 bg-slate-50">
                        {{ $selectedSectionId ? 'No assigned subjects found for this class section.' : 'Select a class section first.' }}
                    </div>
                @endif
            </div>
        </form>
    </div>

    <!-- Step 2: Test Details & Student Marks Register -->
    @if($selectedSectionId && $availableSubjects->isNotEmpty() && $students->isNotEmpty())
        <form action="{{ route('tests.store') }}" method="POST" class="space-y-6" id="marksForm">
            @csrf
            <input type="hidden" name="class_section_id" value="{{ $selectedSectionId }}">

            <!-- Test Details Card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">2. Test Information</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label for="class_section_subject_id" class="block text-xs font-semibold text-slate-700 mb-1">Subject <span class="text-rose-500">*</span></label>
                        <select name="class_section_subject_id" id="class_section_subject_id" required
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-emerald-600">
                            @foreach($availableSubjects as $as)
                                <option value="{{ $as->class_section_subject_id }}" {{ $selectedSubjectId == $as->class_section_subject_id ? 'selected' : '' }}>
                                    {{ $as->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="test_date" class="block text-xs font-semibold text-slate-700 mb-1">Test Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="test_date" id="test_date" value="{{ old('test_date', date('Y-m-d')) }}" required
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-emerald-600">
                    </div>

                    <div>
                        <label for="max_marks" class="block text-xs font-semibold text-slate-700 mb-1">Total Maximum Marks <span class="text-rose-500">*</span></label>
                        <input type="number" name="max_marks" id="max_marks" value="{{ old('max_marks', 20) }}" min="1" max="1000" required
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-emerald-600 font-bold text-slate-800"
                            onchange="validateAllMarks()">
                        <p class="text-[11px] text-slate-500 mt-0.5">e.g. 10, 20, 25, 50, 100</p>
                    </div>

                    <div>
                        <label for="title" class="block text-xs font-semibold text-slate-700 mb-1">Test Title / Description <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-emerald-600"
                            placeholder="e.g. Daily Vocabulary Quiz #4">
                    </div>
                </div>
            </div>

            <!-- Student Marks Table (Class Register Format) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">3. Student Marks Register</h3>
                        <p class="text-xs text-slate-500">Enter obtained marks for each enrolled student (Total: {{ $students->count() }} students)</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="fillAllMarks(0)" class="px-2.5 py-1 text-xs bg-slate-200 text-slate-700 rounded hover:bg-slate-300 font-medium">
                            Set All to 0
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-100 text-slate-700 font-semibold text-xs">
                            <tr>
                                <th class="px-4 py-3 text-left w-12">Sr.#</th>
                                <th class="px-4 py-3 text-left">Photo</th>
                                <th class="px-4 py-3 text-left">Student Name</th>
                                <th class="px-4 py-3 text-left">B-Form Number</th>
                                <th class="px-4 py-3 text-center w-28">Status</th>
                                <th class="px-4 py-3 text-left w-48">Obtained Marks <span class="text-rose-500">*</span></th>
                                <th class="px-4 py-3 text-center w-28">Percentage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($students as $index => $student)
                                <tr id="row_{{ $index }}" class="hover:bg-slate-50 transition {{ old("marks.{$index}.is_absent") ? 'bg-rose-50/40' : '' }}">
                                    <td class="px-4 py-3 text-slate-400 font-mono text-xs">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($student->photo_path)
                                            <img src="{{ asset('storage/' . $student->photo_path) }}" alt="{{ $student->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs">
                                                {{ substr($student->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-bold text-slate-900">
                                        {{ $student->name }}
                                        <input type="hidden" name="marks[{{ $index }}][student_id]" value="{{ $student->id }}">
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-mono text-xs text-slate-500">
                                        {{ $student->b_form_number }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer px-2.5 py-1 rounded-md border border-slate-200 hover:bg-rose-50 hover:border-rose-300 transition text-xs font-semibold text-slate-700 select-none">
                                            <input type="checkbox" 
                                                name="marks[{{ $index }}][is_absent]" 
                                                id="absent_{{ $index }}" 
                                                value="1" 
                                                class="w-4 h-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500" 
                                                {{ old("marks.{$index}.is_absent") ? 'checked' : '' }}
                                                onchange="toggleAbsent({{ $index }})">
                                            <span class="text-rose-700">Absent</span>
                                        </label>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <input type="number" step="0.5" min="0" 
                                                name="marks[{{ $index }}][obtained]" 
                                                id="obtained_{{ $index }}"
                                                value="{{ old("marks.{$index}.obtained", 0) }}" 
                                                class="w-24 px-3 py-1.5 rounded border border-slate-300 font-bold text-slate-900 focus:ring-emerald-600 focus:border-emerald-600 text-sm mark-input disabled:bg-slate-100 disabled:text-slate-400 disabled:border-slate-200"
                                                oninput="calculatePercentage({{ $index }})">
                                            <span class="text-xs text-slate-400" id="max_label_{{ $index }}">/ <span class="max-display">20</span></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <span id="percent_{{ $index }}" class="text-xs font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                            0%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-5 border-t border-slate-200 bg-slate-50 flex items-center justify-end space-x-3">
                    <a href="{{ route('tests.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-800 hover:bg-emerald-900 text-white rounded-lg text-sm font-bold shadow-md transition">
                        Save Test & Marks
                    </button>
                </div>
            </div>
        </form>
    @elseif($selectedSectionId && $availableSubjects->isEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center text-amber-800">
            <h4 class="font-bold text-base mb-1">No Subjects Assigned</h4>
            <p class="text-xs text-amber-700 mb-4">This class section does not have any subjects assigned yet. Please contact you administrator.</p>
            {{-- <a href="{{ route('sections.show', $selectedSectionId) }}" class="inline-flex items-center px-4 py-2 bg-amber-700 text-white text-xs font-semibold rounded-lg">
                Assign Subjects to Class &rarr;
            </a> --}}
        </div>
    @elseif($selectedSectionId && $students->isEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center text-amber-800">
            <h4 class="font-bold text-base mb-1">No Students Enrolled</h4>
            <p class="text-xs text-amber-700 mb-4">There are currently no active students enrolled in this class section.</p>
            @if($isAuthorized)
            <a href="{{ route('students.create', ['class_section_id' => $selectedSectionId]) }}" class="inline-flex items-center px-4 py-2 bg-amber-700 text-white text-xs font-semibold rounded-lg">
                Enroll Students in Class &rarr;
            </a>
            @endif
        </div>
    @endif
</div>

<script>
    function getMaxMarks() {
        const input = document.getElementById('max_marks');
        return input ? parseFloat(input.value) || 20 : 20;
    }

    function toggleAbsent(index) {
        const checkbox = document.getElementById('absent_' + index);
        const obtainedInput = document.getElementById('obtained_' + index);
        const percentSpan = document.getElementById('percent_' + index);
        const row = document.getElementById('row_' + index);
        const isAbsent = checkbox && checkbox.checked;

        if (isAbsent) {
            obtainedInput.dataset.prevValue = obtainedInput.value;
            obtainedInput.value = '';
            obtainedInput.disabled = true;
            obtainedInput.classList.remove('border-rose-500', 'bg-rose-50');
            percentSpan.className = 'text-xs font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-800';
            percentSpan.innerText = 'Absent';
            if (row) row.classList.add('bg-rose-50/40');
        } else {
            obtainedInput.disabled = false;
            if (obtainedInput.value === '') {
                obtainedInput.value = obtainedInput.dataset.prevValue || '0';
            }
            if (row) row.classList.remove('bg-rose-50/40');
            calculatePercentage(index);
        }
    }

    function calculatePercentage(index) {
        const checkbox = document.getElementById('absent_' + index);
        if (checkbox && checkbox.checked) {
            return;
        }

        const max = getMaxMarks();
        const obtainedInput = document.getElementById('obtained_' + index);
        const percentSpan = document.getElementById('percent_' + index);
        
        if (!obtainedInput || !percentSpan) return;
        
        let obtained = parseFloat(obtainedInput.value) || 0;
        
        if (obtained > max) {
            obtainedInput.classList.add('border-rose-500', 'bg-rose-50');
            percentSpan.className = 'text-xs font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-800';
            percentSpan.innerText = 'Exceeds Max!';
            return;
        } else {
            obtainedInput.classList.remove('border-rose-500', 'bg-rose-50');
        }

        let pct = max > 0 ? Math.round((obtained / max) * 100) : 0;
        percentSpan.innerText = pct + '%';
        
        if (pct >= 80) {
            percentSpan.className = 'text-xs font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800';
        } else if (pct >= 50) {
            percentSpan.className = 'text-xs font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800';
        } else {
            percentSpan.className = 'text-xs font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-800';
        }
    }

    function validateAllMarks() {
        const max = getMaxMarks();
        document.querySelectorAll('.max-display').forEach(el => el.innerText = max);
        const inputs = document.querySelectorAll('.mark-input');
        inputs.forEach((input, idx) => {
            const checkbox = document.getElementById('absent_' + idx);
            if (checkbox && checkbox.checked) {
                toggleAbsent(idx);
            } else {
                calculatePercentage(idx);
            }
        });
    }

    function fillAllMarks(value) {
        const inputs = document.querySelectorAll('.mark-input');
        inputs.forEach((input, idx) => {
            const checkbox = document.getElementById('absent_' + idx);
            if (!checkbox || !checkbox.checked) {
                input.value = value;
                calculatePercentage(idx);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        validateAllMarks();
    });
</script>
@endsection
