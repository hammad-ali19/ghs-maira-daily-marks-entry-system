@extends('layouts.app')

@section('title', 'Add New Subject')
@section('subtitle', 'Add a reusable academic subject to the curriculum catalogue')

@section('actions')
    <a href="{{ route('subjects.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
        &larr; Back to Subjects
    </a>
@endsection

@section('content')
<div class="max-w-md bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <form action="{{ route('subjects.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Subject Title <span class="text-rose-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 text-sm"
                placeholder="e.g. Mathematics, Urdu, English, General Science">
            <p class="text-xs text-slate-500 mt-1">Once added, this subject can be assigned to multiple class sections.</p>
        </div>

        <div class="border-t border-slate-200 pt-5 flex items-center justify-end space-x-3">
            <a href="{{ route('subjects.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2 bg-emerald-800 hover:bg-emerald-900 text-white rounded-lg text-sm font-medium shadow-sm transition">
                Save Subject
            </button>
        </div>
    </form>
</div>
@endsection
