@extends('layouts.app')

@section('title', 'Edit School Class: ' . $class->name)
@section('subtitle', 'Update class information and manage sections')

@section('actions')
    <a href="{{ route('classes.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
        &larr; Back to Classes
    </a>
@endsection

@section('content')
<div class="max-w-3xl bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <form action="{{ route('classes.update', $class) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Class Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $class->name) }}" required
                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600 text-sm">
            </div>

            <div>
                <label for="display_order" class="block text-sm font-medium text-slate-700 mb-1">Display Order</label>
                <input type="number" name="display_order" id="display_order" value="{{ old('display_order', $class->display_order) }}" min="0"
                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600 text-sm">
            </div>

            <div>
                <label for="is_active" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="is_active" id="is_active" class="w-full px-3.5 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600 text-sm">
                    <option value="1" {{ old('is_active', $class->is_active) ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !old('is_active', $class->is_active) ? 'selected' : '' }}>Inactive / Deactivated</option>
                </select>
            </div>
        </div>

        <!-- Sections List -->
        <div class="border-t border-slate-200 pt-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Class Sections</h3>
                    <p class="text-xs text-slate-500">Edit existing sections or add new sections to this class.</p>
                </div>
                <button type="button" id="addNewSectionRowBtn" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 bg-emerald-50 px-2.5 py-1 rounded border border-emerald-200">
                    + Add New Section
                </button>
            </div>

            <div id="sectionsEditContainer" class="space-y-3">
                @foreach($class->sections as $index => $section)
                    <div class="section-row flex items-center gap-3 p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <input type="hidden" name="sections[{{ $index }}][id]" value="{{ $section->id }}">
                        
                        <div class="w-1/4">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Section Name</label>
                            <input type="text" name="sections[{{ $index }}][name]" value="{{ old("sections.{$index}.name", $section->name) }}" required
                                class="w-full px-3 py-1.5 rounded border border-slate-300 text-sm focus:ring-emerald-600 focus:border-emerald-600">
                        </div>

                        <div class="flex-1">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Class Incharge Teacher</label>
                            <select name="sections[{{ $index }}][incharge_id]" class="w-full px-3 py-1.5 rounded border border-slate-300 text-sm focus:ring-emerald-600 focus:border-emerald-600">
                                <option value="">-- No Incharge --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old("sections.{$index}.incharge_id", $section->incharge_id) == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }} ({{ $teacher->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-28">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                            <select name="sections[{{ $index }}][is_active]" class="w-full px-2 py-1.5 rounded border border-slate-300 text-sm">
                                <option value="1" {{ $section->is_active ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !$section->is_active ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-slate-200 pt-5 flex items-center justify-end space-x-3">
            <a href="{{ route('classes.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2 bg-emerald-800 hover:bg-emerald-900 text-white rounded-lg text-sm font-medium shadow-sm transition">
                Update Class & Sections
            </button>
        </div>
    </form>
</div>

<script>
    let newSectionIndex = {{ $class->sections->count() }};
    const teachersOptions = `@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }} ({{ $teacher->email }})</option>@endforeach`;

    document.getElementById('addNewSectionRowBtn').addEventListener('click', function () {
        const div = document.createElement('div');
        div.className = 'section-row flex items-center gap-3 p-3 bg-emerald-50/50 rounded-lg border border-emerald-200';
        div.innerHTML = `
            <div class="w-1/4">
                <label class="block text-xs font-medium text-slate-600 mb-1">New Section</label>
                <input type="text" name="sections[${newSectionIndex}][name]" value="" required
                    class="w-full px-3 py-1.5 rounded border border-slate-300 text-sm focus:ring-emerald-600 focus:border-emerald-600"
                    placeholder="e.g. C">
            </div>
            <div class="flex-1">
                <label class="block text-xs font-medium text-slate-600 mb-1">Class Incharge Teacher</label>
                <select name="sections[${newSectionIndex}][incharge_id]" class="w-full px-3 py-1.5 rounded border border-slate-300 text-sm focus:ring-emerald-600 focus:border-emerald-600">
                    <option value="">-- No Incharge --</option>
                    ${teachersOptions}
                </select>
            </div>
            <div class="w-28">
                <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                <select name="sections[${newSectionIndex}][is_active]" class="w-full px-2 py-1.5 rounded border border-slate-300 text-sm">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="pt-5">
                <button type="button" class="text-rose-600 hover:text-rose-800 p-1.5 rounded hover:bg-rose-50" onclick="this.closest('.section-row').remove()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        `;
        document.getElementById('sectionsEditContainer').appendChild(div);
        newSectionIndex++;
    });
</script>
@endsection
