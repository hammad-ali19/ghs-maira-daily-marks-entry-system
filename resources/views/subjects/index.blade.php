@extends('layouts.app')

@section('title', 'Subject Catalogue')
@section('subtitle', 'Global reusable curriculum subjects assigned across classes')

@section('actions')
    <a href="{{ route('subjects.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-800 hover:bg-emerald-900 text-white text-sm font-medium rounded-lg shadow-sm transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Subject
    </a>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden max-w-4xl">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-slate-700 font-semibold">
                <tr>
                    <th class="px-5 py-3 text-left">Subject Name</th>
                    <th class="px-5 py-3 text-center">Assigned to Classes</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($subjects as $subject)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-5 py-4 whitespace-nowrap font-bold text-slate-900 text-base">
                            {{ $subject->name }}
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800">
                                {{ $subject->class_section_subjects_count }} {{ Str::plural('class', $subject->class_section_subjects_count) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-center">
                            @if($subject->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">Active</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-right space-x-2 text-xs">
                            <a href="{{ route('subjects.edit', $subject) }}" class="text-emerald-700 hover:text-emerald-900 font-semibold">
                                Edit
                            </a>
                            <form action="{{ route('subjects.destroy', $subject) }}" method="POST" class="inline" onsubmit="return confirm('Delete subject {{ $subject->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-900 font-medium">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-slate-500">
                            No subjects registered in catalogue yet. Click "Add Subject" to begin.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($subjects->hasPages())
        <div class="px-5 py-3 border-t border-slate-200 bg-slate-50">
            {{ $subjects->links() }}
        </div>
    @endif
</div>
@endsection
