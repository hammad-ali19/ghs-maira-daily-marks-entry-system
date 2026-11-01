@extends('layouts.app')

@section('title', 'Daily Tests & Marks Register')
@section('subtitle', 'Search, view and manage daily test records across all classes')

@section('actions')
    <a href="{{ route('tests.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-800 hover:bg-emerald-900 text-white text-sm font-medium rounded-lg shadow-sm transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Record New Marks
    </a>
@endsection

@section('content')
<div class="space-y-4">
    <!-- Filter Bar -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('tests.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Class Section</label>
                <select name="class_section_id" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600">
                    <option value="">-- All Classes --</option>
                    @foreach($sections as $sec)
                        <option value="{{ $sec->id }}" {{ request('class_section_id') == $sec->id ? 'selected' : '' }}>
                            {{ $sec->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Subject</label>
                <select name="subject_id" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600">
                    <option value="">-- All Subjects --</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>
                            {{ $sub->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-medium rounded-lg shadow-xs transition">
                    Filter
                </button>
                @if(request('class_section_id') || request('subject_id') || request('date_from') || request('date_to'))
                    <a href="{{ route('tests.index') }}" class="px-3 py-2 border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-medium rounded-lg">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tests List Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-700 font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Class & Section</th>
                        <th class="px-4 py-3 text-left">Subject</th>
                        <th class="px-4 py-3 text-left">Test Title / Description</th>
                        <th class="px-4 py-3 text-center">Max Marks</th>
                        <th class="px-4 py-3 text-center">Students Tested</th>
                        <th class="px-4 py-3 text-left">Conducted By</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tests as $test)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5 whitespace-nowrap font-medium text-slate-900">
                                {{ $test->test_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                    {{ $test->classSectionSubject->classSection->full_name }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap font-bold text-slate-800">
                                {{ $test->classSectionSubject->subject->name }}
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">
                                {{ $test->title }}
                            </td>
                            <td class="px-4 py-3.5 text-center font-bold text-slate-900">
                                {{ $test->max_marks }}
                            </td>
                            <td class="px-4 py-3.5 text-center text-slate-700 font-semibold">
                                {{ $test->results->count() }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-500 whitespace-nowrap">
                                {{ $test->creator->name }}
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-2 text-xs">
                                <a href="{{ route('tests.show', $test) }}" class="text-emerald-700 hover:text-emerald-900 font-bold">
                                    View Marks
                                </a>
                                <a href="{{ route('reports.test', $test) }}" class="text-slate-600 hover:text-slate-900 font-medium">
                                    Report
                                </a>
                                <a href="{{ route('reports.test.pdf', $test) }}" class="text-slate-500 hover:text-slate-800 font-medium">
                                    PDF
                                </a>
                                @if(auth()->user()->isAdmin())
                                <form class="inline" action="{{route('tests.destroy', $test)}}" method="POST" onsubmit="return confirm('Delete this test?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-500 cursor-pointer">
                                       Delete
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-slate-500">
                                No tests found. Click "Record New Marks" to add a daily test.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tests->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                {{ $tests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
