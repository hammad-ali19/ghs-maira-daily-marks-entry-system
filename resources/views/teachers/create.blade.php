@extends('layouts.app')

@section('title', 'Register New Teacher')
@section('subtitle', 'Create login credentials for a school teacher')

@section('actions')
    <a href="{{ route('teachers.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
        &larr; Back to Teachers
    </a>
@endsection

@section('content')
<div class="max-w-xl bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <form action="{{ route('teachers.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Teacher Full Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 text-sm"
                placeholder="e.g. Muhammad Aslam, SST">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address (Login ID) <span class="text-rose-500">*</span></label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 text-sm"
                placeholder="aslam@ghsmaira.edu.pk">
            <p class="text-xs text-slate-500 mt-1">This email address will be used to log in.</p>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Temporary Initial Password <span class="text-rose-500">*</span></label>
            <input type="password" name="password" id="password" required minlength="8"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 text-sm"
                placeholder="Minimum 8 characters">
            <p class="text-xs text-slate-500 mt-1">Assign an initial password to communicate securely to the teacher.</p>
        </div>

        <div class="border-t border-slate-200 pt-5 flex items-center justify-end space-x-3">
            <a href="{{ route('teachers.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2 bg-emerald-800 hover:bg-emerald-900 text-white rounded-lg text-sm font-medium shadow-sm transition">
                Create Teacher Account
            </button>
        </div>
    </form>
</div>
@endsection
