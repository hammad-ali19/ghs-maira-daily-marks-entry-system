@extends('layouts.app')

@section('title', 'Classes & Sections')
@section('subtitle', 'Configure grades and class sections dynamically (e.g. KG, Prep, 1st to 10th)')

@section('actions')
    <a href="{{ route('classes.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-800 hover:bg-emerald-900 text-white text-sm font-medium rounded-lg shadow-sm transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add New Class
    </a>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-slate-700 font-semibold">
                <tr>
                    <th class="px-5 py-3 text-left">Order</th>
                    <th class="px-5 py-3 text-left">Class Name</th>
                    <th class="px-5 py-3 text-left">Sections & Incharges</th>
                    <th class="px-5 py-3 text-center">Total Students</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($classes as $class)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-5 py-4 text-slate-400 font-mono text-xs">{{ $class->display_order }}</td>
                        <td class="px-5 py-4 font-bold text-slate-900 text-base">
                            <a href="{{ route('classes.show', $class) }}" class="hover:text-emerald-700">
                                {{ $class->name }}
                            </a>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-2">
                                @forelse($class->sections as $section)
                                    <a href="{{ route('sections.show', $section) }}" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium border transition {{ $section->is_active ? 'bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                                        <span class="font-bold mr-1">{{ $section->name }}:</span>
                                        <span class="text-slate-600">{{ $section->incharge ? $section->incharge->name : 'No Incharge' }}</span>
                                    </a>
                                @empty
                                    <span class="text-xs text-slate-400 italic">No sections created</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center font-bold text-slate-800">
                            {{ $class->sections->sum(fn($s) => $s->currentEnrollments->count()) }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($class->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">Active</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap space-x-2">
                            <a href="{{ route('classes.show', $class) }}" class="text-slate-600 hover:text-slate-900 font-medium text-xs">View</a>
                            <a href="{{ route('classes.edit', $class) }}" class="text-emerald-700 hover:text-emerald-900 font-medium text-xs">Edit</a>
                            <form action="{{ route('classes.destroy', $class) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to deactivate/delete this class? Historical data will be protected.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-900 font-medium text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                            No classes created yet. Click "Add New Class" to begin configuring school classes.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($classes->hasPages())
        <div class="px-5 py-3 border-t border-slate-200 bg-slate-50">
            {{ $classes->links() }}
        </div>
    @endif
</div>
@endsection
