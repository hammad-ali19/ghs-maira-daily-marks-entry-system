@extends('layouts.app')

@section('title', 'Edit Teacher: ' . $teacher->name)
@section('subtitle', 'Update account information or reset credentials')

@section('actions')
    <a href="{{ route('teachers.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
        &larr; Back to Teachers
    </a>
@endsection

@section('content')
<div class="max-w-xl bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <form id="{{ $teacher->id }}-update-form" action="{{ route('teachers.update', $teacher) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Teacher Full Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name', $teacher->name) }}" required
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 text-sm">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address (Login ID) <span class="text-rose-500">*</span></label>
            <input type="email" name="email" id="email" value="{{ old('email', $teacher->email) }}" required
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 text-sm">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Reset Password</label>
            <input type="password" name="password" id="password" minlength="8"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 text-sm"
                placeholder="Leave blank to keep existing password">
            <p class="text-xs text-slate-500 mt-1">Only fill this if resetting the teacher's password.</p>
        </div>

        <div>
        <label for="is_active" class="block text-sm font-medium text-slate-700 mb-1">Account Status</label>
        <select name="is_active" id="is_active" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 text-sm">
            <option value="1" {{ old('is_active', $teacher->is_active) ? 'selected' : '' }}>Active (Can log in)</option>
            <option value="0" {{ !old('is_active', $teacher->is_active) ? 'selected' : '' }}>Deactivated (Access revoked)</option>
        </select>
    </div>

    <div class="border-t border-slate-200 pt-5 flex items-center justify-end space-x-3">
        <a href="{{ route('teachers.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
            Cancel
        </a>
        <button type="submit" class="px-5 py-2 bg-emerald-800 hover:bg-emerald-900 text-white rounded-lg text-sm font-medium shadow-sm transition">
            Update Teacher Account
        </button>
    </div>
</form>

</div>

    <form class="mt-3" action="{{ route('teachers.destroy', $teacher) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this teacher? They will be removed as incharge of any class sections and unassigned from all subjects.')">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-4 py-2 border border-rose-200 rounded-lg text-sm font-medium text-rose-600 hover:bg-rose-50 hover:text-rose-900">
            Delete Teacher
        </button>
    </form>
@endsection
