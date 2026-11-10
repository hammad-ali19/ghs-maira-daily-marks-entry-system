@extends('layouts.app')

@section('title', 'Edit Test Marks: ' . $test->title)
@section('subtitle', 'Update test metadata or correct student marks')

@section('actions')
    <a href="{{ route('tests.show', $test) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
        &larr; Cancel & View Test
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <form action="{{ route('tests.update', $test) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Test Metadata -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Test Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Class Section</label>
                    <input type="text" disabled value="{{ $test->classSectionSubject->classSection->full_name }}"
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-100 text-sm text-slate-600 font-medium">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Subject</label>
                    <input type="text" disabled value="{{ $test->classSectionSubject->subject->name }}"
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-100 text-sm text-slate-600 font-medium">
                </div>

                <div>
                    <label for="test_date" class="block text-xs font-semibold text-slate-700 mb-1">Test Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="test_date" id="test_date" value="{{ old('test_date', $test->test_date->format('Y-m-d')) }}" required
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-emerald-600">
                </div>

                <div>
                    <label for="max_marks" class="block text-xs font-semibold text-slate-700 mb-1">Maximum Marks <span class="text-rose-500">*</span></label>
                    <input type="number" name="max_marks" id="max_marks" value="{{ old('max_marks', $test->max_marks) }}" min="1" max="1000" required
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-emerald-600 font-bold"
                        onchange="validateAllEditMarks()">
                </div>

                <div class="sm:col-span-4">
                    <label for="title" class="block text-xs font-semibold text-slate-700 mb-1">Test Title / Description <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title', $test->title) }}" required
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-emerald-600">
                </div>
            </div>
        </div>

        <!-- Student Marks Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="font-bold text-slate-900 text-base">Student Marks Register</h3>
                <p class="text-xs text-slate-500">Edit individual student obtained marks</p>
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
                        @foreach($results as $index => $result)
                            @php 
                                $student = $result->student; 
                                $isAbsent = old("marks.{$index}.is_absent", $result->is_absent);
                            @endphp
                            <tr id="row_{{ $index }}" class="hover:bg-slate-50 transition {{ $isAbsent ? 'bg-rose-50/40' : '' }}">
                                <td class="px-4 py-3 text-slate-400 font-mono text-xs">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($student->photo_path)
                                        <img src="{{ app()->environment('production') ? $student->photo_path : asset('storage/' . $student->photo_path)  }}" class="w-8 h-8 rounded-full object-cover border border-slate-200">
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
                                            {{ $isAbsent ? 'checked' : '' }}
                                            onchange="toggleEditAbsent({{ $index }})">
                                        <span class="text-rose-700">Absent</span>
                                    </label>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <input type="number" step="0.5" min="0" 
                                            name="marks[{{ $index }}][obtained]" 
                                            id="obtained_{{ $index }}"
                                            value="{{ old("marks.{$index}.obtained", $result->is_absent ? '' : $result->obtained_marks) }}" 
                                            class="w-24 px-3 py-1.5 rounded border border-slate-300 font-bold text-slate-900 focus:ring-emerald-600 focus:border-emerald-600 text-sm mark-edit-input disabled:bg-slate-100 disabled:text-slate-400 disabled:border-slate-200"
                                            oninput="calculateEditPercentage({{ $index }})" required>
                                        <span class="text-xs text-slate-400">/ <span class="max-display">{{ $test->max_marks }}</span></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span id="percent_{{ $index }}" class="text-xs font-bold px-2 py-0.5 rounded {{ $isAbsent ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $isAbsent ? 'Absent' : ($result->percentage !== null ? $result->percentage.'%' : '0%') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-5 border-t border-slate-200 bg-slate-50 flex items-center justify-end space-x-3">
                <a href="{{ route('tests.show', $test) }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-800 hover:bg-emerald-900 text-white rounded-lg text-sm font-bold shadow-md transition">
                    Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function getEditMaxMarks() {
        const input = document.getElementById('max_marks');
        return input ? parseFloat(input.value) || 20 : 20;
    }

    function toggleEditAbsent(index) {
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
            calculateEditPercentage(index);
        }
    }

    function calculateEditPercentage(index) {
        const checkbox = document.getElementById('absent_' + index);
        if (checkbox && checkbox.checked) {
            return;
        }

        const max = getEditMaxMarks();
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

    function validateAllEditMarks() {
        const max = getEditMaxMarks();
        document.querySelectorAll('.max-display').forEach(el => el.innerText = max);
        const inputs = document.querySelectorAll('.mark-edit-input');
        inputs.forEach((input, idx) => {
            const checkbox = document.getElementById('absent_' + idx);
            if (checkbox && checkbox.checked) {
                toggleEditAbsent(idx);
            } else {
                calculateEditPercentage(idx);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        validateAllEditMarks();
    });
</script>
@endsection
