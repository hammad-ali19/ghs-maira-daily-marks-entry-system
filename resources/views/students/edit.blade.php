@extends('layouts.app')

@section('title', 'Edit Student: ' . $student->name)
@section('subtitle', 'Update student identity, current class section or photograph')

@section('actions')
    <a href="{{ route('students.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
        &larr; Back to Students
    </a>
@endsection

@section('content')
<div class="max-w-2xl bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <form action="{{ route('students.update', $student) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
            @if($student->photo_path)
                <img src="{{ $student->photo_path }}" alt="{{ $student->name }}" class="w-16 h-16 rounded-full object-cover border-2 border-emerald-600 shadow-sm">
            @endif
            <div>
                <p class="font-bold text-slate-900">{{ $student->name }}</p>
                <p class="text-xs font-mono text-slate-500">B-Form: {{ $student->b_form_number }}</p>
                <p class="text-xs text-emerald-700 font-semibold mt-0.5">Current: {{ $currentSection?->full_name ?? 'Not Enrolled' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Student Full Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $student->name) }}" required
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 text-sm">
            </div>

            <div>
                <label for="b_form_number" class="block text-sm font-medium text-slate-700 mb-1">B-Form / CNIC Number <span class="text-rose-500">*</span></label>
                <input type="text" name="b_form_number" id="b_form_number" value="{{ old('b_form_number', $student->b_form_number) }}" required
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 text-sm font-mono">
            </div>

            <div>
                <label for="class_section_id" class="block text-sm font-medium text-slate-700 mb-1">Class Section <span class="text-rose-500">*</span></label>
                <select name="class_section_id" id="class_section_id" required
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 text-sm">
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" {{ old('class_section_id', $currentSection?->id) == $section->id ? 'selected' : '' }}>
                            {{ $section->full_name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500 mt-1">If changed, old class history & tests will be automatically preserved.</p>
            </div>

            <div>
                <label for="is_active" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="is_active" id="is_active" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm">
                    <option value="1" {{ old('is_active', $student->is_active) ? 'selected' : '' }}>Active Student</option>
                    <option value="0" {{ !old('is_active', $student->is_active) ? 'selected' : '' }}>Inactive / Left School</option>
                </select>
            </div>
        </div>

        <div>
            <label for="photo" class="block text-sm font-medium text-slate-700 mb-1">Change Photograph</label>
            
            <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/jpg,image/webp"
                class="block text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            
            <p class="text-xs text-slate-500 mt-1">Leave empty to keep existing photo.</p>
        </div>

        <div class="border-t border-slate-200 pt-5 flex items-center justify-end space-x-3">
            <a href="{{ route('students.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2 bg-emerald-800 hover:bg-emerald-900 text-white rounded-lg text-sm font-medium shadow-sm transition">
                Update Student
            </button>
        </div>
    </form>
</div>
@endsection
