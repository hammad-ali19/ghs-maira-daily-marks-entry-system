@extends('layouts.app')

@section('title', 'Teacher Accounts Management')
@section('subtitle', 'Manage school faculty logins, permissions and account status')

@section('actions')
    <a href="{{ route('teachers.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-800 hover:bg-emerald-900 text-white text-sm font-medium rounded-lg shadow-sm transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3-3h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
        </svg>
        Register New Teacher
    </a>
@endsection

@section('content')
<div class="space-y-4">
    <!-- Search and Filters -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('teachers.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search teacher by name or email..."
                    class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-600">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium rounded-lg shadow-xs transition">
                Search
            </button>
            @if(request('search'))
                <a href="{{ route('teachers.index') }}" class="px-3 py-2 border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm font-medium rounded-lg transition">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Teachers Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-700 font-semibold">
                    <tr>
                        <th class="px-5 py-3 text-left">Teacher Name</th>
                        <th class="px-5 py-3 text-left">Email Address (Login ID)</th>
                        <th class="px-5 py-3 text-left">Class Incharge Role</th>
                        <th class="px-5 py-3 text-left">Assigned Subjects</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($teachers as $teacher)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 whitespace-nowrap font-bold text-slate-900">
                                {{ $teacher->name }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-slate-600 font-mono text-xs">
                                {{ $teacher->email }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @forelse($teacher->inchargeOf as $section)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800 mr-1">
                                        {{ $section->full_name }}
                                    </span>
                                @empty
                                    <span class="text-xs text-slate-400 italic">None</span>
                                @endforelse
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @forelse($teacher->teacherAssignments as $ta)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $ta->classSectionSubject->subject->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400 italic">None</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                @if($teacher->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        Deactivated
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right space-x-2 text-xs">
                                <a href="{{ route('teachers.edit', $teacher) }}" class="text-emerald-700 hover:text-emerald-900 font-semibold">
                                    Edit / Reset Password
                                </a>
                                <form action="{{ route('teachers.toggle', $teacher) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="{{ $teacher->is_active ? 'text-amber-700 hover:text-amber-900' : 'text-emerald-700 hover:text-emerald-900' }} font-medium">
                                        {{ $teacher->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-500">
                                No teacher accounts found. Click "Register New Teacher" to create faculty credentials.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($teachers->hasPages())
            <div class="px-5 py-3 border-t border-slate-200 bg-slate-50">
                {{ $teachers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
