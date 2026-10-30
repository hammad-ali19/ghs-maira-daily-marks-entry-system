@extends('layouts.app')

@section('title', 'Add New School Class')
@section('subtitle', 'Create a new grade or class level and define its initial sections')

@section('actions')
    <a href="{{ route('classes.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
        &larr; Back to Classes
    </a>
@endsection

@section('content')
<div class="max-w-2xl bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <form action="{{ route('classes.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Class Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600 text-sm"
                    placeholder="e.g. KG, Prep, 6th, 10th">
                <p class="text-xs text-slate-500 mt-1">Specify grade or class level (e.g. 6th, 7th, 8th, Matric).</p>
            </div>

            <div>
                <label for="display_order" class="block text-sm font-medium text-slate-700 mb-1">Display Order</label>
                <input type="number" name="display_order" id="display_order" value="{{ old('display_order', 1) }}" min="0"
                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600 text-sm">
                <p class="text-xs text-slate-500 mt-1">Sort order in lists.</p>
            </div>
        </div>

        <!-- Sections Configuration -->
        <div class="border-t border-slate-200 pt-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Class Sections</h3>
                    <p class="text-xs text-slate-500">Each section will have its own students, subjects, and incharge.</p>
                </div>
                <button type="button" id="addSectionRowBtn" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 bg-emerald-50 px-2.5 py-1 rounded border border-emerald-200">
                    + Add Another Section
                </button>
            </div>

            <div id="sectionsContainer" class="space-y-3">
                <div class="section-row flex items-center gap-3 p-3 bg-slate-50 rounded-lg border border-slate-200">
                    <div class="w-1/3">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Section Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="sections[0][name]" value="{{ old('sections.0.name', 'A') }}" required
                            class="w-full px-3 py-1.5 rounded border border-slate-300 text-sm focus:ring-emerald-600 focus:border-emerald-600"
                            placeholder="e.g. A, B, or Default">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Designated Incharge Teacher</label>
                        <select name="sections[0][incharge_id]" class="w-full px-3 py-1.5 rounded border border-slate-300 text-sm focus:ring-emerald-600 focus:border-emerald-600">
                            <option value="">-- Assign Incharge Later --</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('sections.0.incharge_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }} ({{ $teacher->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200 pt-5 flex items-center justify-end space-x-3">
            <a href="{{ route('classes.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2 bg-emerald-800 hover:bg-emerald-900 text-white rounded-lg text-sm font-medium shadow-sm transition">
                Save School Class
            </button>
        </div>
    </form>
</div>

<script>
    let sectionCount = 1;
    const teachersOptions = `@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }} ({{ $teacher->email }})</option>@endforeach`;

    document.getElementById('addSectionRowBtn').addEventListener('click', function () {
        const nextLetter = String.fromCharCode(65 + (sectionCount % 26));
        const div = document.createElement('div');
        div.className = 'section-row flex items-center gap-3 p-3 bg-slate-50 rounded-lg border border-slate-200';
        div.innerHTML = `
            <div class="w-1/3">
                <label class="block text-xs font-medium text-slate-600 mb-1">Section Name <span class="text-rose-500">*</span></label>
                <input type="text" name="sections[${sectionCount}][name]" value="${nextLetter}" required
                    class="w-full px-3 py-1.5 rounded border border-slate-300 text-sm focus:ring-emerald-600 focus:border-emerald-600"
                    placeholder="e.g. B, C">
            </div>
            <div class="flex-1">
                <label class="block text-xs font-medium text-slate-600 mb-1">Designated Incharge Teacher</label>
                <select name="sections[${sectionCount}][incharge_id]" class="w-full px-3 py-1.5 rounded border border-slate-300 text-sm focus:ring-emerald-600 focus:border-emerald-600">
                    <option value="">-- Assign Incharge Later --</option>
                    ${teachersOptions}
                </select>
            </div>
            <div class="pt-5">
                <button type="button" class="text-rose-600 hover:text-rose-800 p-1.5 rounded hover:bg-rose-50" onclick="this.closest('.section-row').remove()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        `;
        document.getElementById('sectionsContainer').appendChild(div);
        sectionCount++;
    });
</script>
@endsection
