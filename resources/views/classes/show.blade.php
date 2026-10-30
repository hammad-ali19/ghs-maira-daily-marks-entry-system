@extends('layouts.app')

@section('title', 'Class: ' . $class->name)
@section('subtitle', 'Class details, sections and assigned incharges')

@section('actions')
    @if(auth()->user()->isAdmin())
        <a href="{{ route('classes.edit', $class) }}" class="px-3.5 py-1.5 bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-semibold rounded-lg shadow-sm">
            Edit Class
        </a>
    @endif
    <a href="{{ route('classes.index') }}" class="text-xs font-medium text-slate-600 hover:text-slate-900">
        &larr; All Classes
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Class Grade</span>
                <h2 class="text-2xl font-bold text-slate-900">{{ $class->name }}</h2>
            </div>
            <div class="flex items-center space-x-4 text-sm">
                <div>
                    <span class="text-slate-500">Status:</span>
                    @if($class->is_active)
                        <span class="font-semibold text-emerald-700">Active</span>
                    @else
                        <span class="font-semibold text-slate-500">Inactive</span>
                    @endif
                </div>
                <div>
                    <span class="text-slate-500">Display Order:</span>
                    <span class="font-semibold text-slate-800">{{ $class->display_order }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Total Sections:</span>
                    <span class="font-semibold text-slate-800">{{ $class->sections->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sections Grid -->
    <div>
        <h3 class="text-base font-bold text-slate-900 mb-4">Class Sections</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($class->sections as $section)
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 hover:border-emerald-500 transition">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-xs text-slate-400 font-semibold uppercase">Section</span>
                            <h4 class="text-xl font-bold text-emerald-900">{{ $section->full_name }}</h4>
                        </div>
                        @if($section->is_active)
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">Active</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-600">Inactive</span>
                        @endif
                    </div>

                    <div class="mt-4 space-y-2 text-xs text-slate-600 border-t border-slate-100 pt-3">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Class Incharge:</span>
                            <span class="font-medium text-slate-900">{{ $section->incharge ? $section->incharge->name : 'Unassigned' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Enrolled Students:</span>
                            <span class="font-bold text-slate-900">{{ $section->currentEnrollments->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Assigned Subjects:</span>
                            <span class="font-bold text-slate-900">{{ $section->classSectionSubjects->count() }}</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <a href="{{ route('sections.show', $section) }}" class="text-xs font-bold text-emerald-800 hover:text-emerald-950 inline-flex items-center">
                            Manage Students & Subjects &rarr;
                        </a>
                        <a href="{{ route('tests.create', ['class_section_id' => $section->id]) }}" class="text-xs text-slate-600 hover:text-emerald-700 font-medium">
                            Record Marks
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white p-8 rounded-xl border border-slate-200 text-center text-slate-500">
                    No sections configured for this class.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
