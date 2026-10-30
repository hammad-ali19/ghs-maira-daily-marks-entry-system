@extends('layouts.app')

@section('title', 'Enroll New Student')
@section('subtitle', 'Register a new student with national B-Form number and photograph')

@section('actions')
    <a href="{{ route('students.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
        &larr; Back to Students
    </a>
@endsection

@section('content')
<div class="max-w-2xl bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Student Full Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 text-sm"
                    placeholder="e.g. Ali Ahmed">
            </div>

            <div>
                <label for="b_form_number" class="block text-sm font-medium text-slate-700 mb-1">B-Form / CNIC Number <span class="text-rose-500">*</span></label>
                <input type="text" name="b_form_number" id="b_form_number" value="{{ old('b_form_number') }}" required
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 text-sm font-mono"
                    placeholder="e.g. 37405-1234567-1">
                <p class="text-xs text-slate-500 mt-1">Must be unique for each student.</p>
            </div>

            <div>
                <label for="class_section_id" class="block text-sm font-medium text-slate-700 mb-1">Enrolled Class Section <span class="text-rose-500">*</span></label>
                <select name="class_section_id" id="class_section_id" required
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 text-sm">
                    <option value="">-- Select Class Section --</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" {{ (old('class_section_id') ?? $preselectedSectionId) == $section->id ? 'selected' : '' }}>
                            {{ $section->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label for="photo" class="block text-sm font-medium text-slate-700 mb-1">Student Photograph <span class="text-rose-500">*</span></label>
            <div class="mt-1 flex items-center gap-4">
                <input type="file" name="photo" id="photo" required accept="image/jpeg,image/png,image/jpg,image/webp"
                    class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            </div>
            <p class="text-xs text-slate-500 mt-1">Photograph is required for official identity records. Max size: 2MB.</p>
        </div>

        <div class="border-t border-slate-200 pt-5 flex items-center justify-end space-x-3">
            <a href="{{ route('students.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2 bg-emerald-800 hover:bg-emerald-900 text-white rounded-lg text-sm font-medium shadow-sm transition">
                Register Student
            </button>
        </div>
    </form>
</div>
@endsection
