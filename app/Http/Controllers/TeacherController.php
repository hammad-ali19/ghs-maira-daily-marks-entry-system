<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeacherController extends Controller
{
    /**
     * Display a listing of teacher accounts.
     */
    public function index(Request $request): View
    {
        $query = User::where('role', 'teacher')->with(['inchargeOf.schoolClass', 'teacherAssignments.classSectionSubject.subject']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $teachers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('teachers.index', compact('teachers'));
    }

    /**
     * Show the form for creating a new teacher account.
     */
    public function create(): View
    {
        return view('teachers.create');
    }

    /**
     * Store a newly created teacher account in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        return redirect()->route('teachers.index')->with('success', "Teacher account for {$validated['name']} created successfully.");
    }

    /**
     * Show the form for editing the teacher account.
     */
    public function edit(User $teacher): View
    {
        if ($teacher->role !== 'teacher') {
            abort(404);
        }

        return view('teachers.edit', compact('teacher'));
    }

    /**
     * Update the teacher account.
     */
    public function update(Request $request, User $teacher): RedirectResponse
    {
        if ($teacher->role !== 'teacher') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($teacher->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['required', 'boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => (bool) $validated['is_active'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $teacher->update($data);

        return redirect()->route('teachers.index')->with('success', "Teacher '{$teacher->name}' updated successfully.");
    }

    /**
     * Toggle teacher active / deactivated status.
     */
    public function toggleStatus(User $teacher): RedirectResponse
    {
        if ($teacher->role !== 'teacher') {
            abort(404);
        }

        $teacher->update([
            'is_active' => ! $teacher->is_active,
        ]);

        $status = $teacher->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Teacher '{$teacher->name}' has been {$status}.");
    }

    /**
     * Delete a teacher account (Admin only).
     */
    public function destroy(Request $request, User $teacher): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Only administrators can delete teacher accounts.');
        }

        if ($teacher->role !== 'teacher') {
            abort(404);
        }

        // Remove as incharge of any class sections
        ClassSection::where('incharge_id', $teacher->id)->update(['incharge_id' => null]);

        // Remove teacher assignments for subjects
        TeacherAssignment::where('user_id', $teacher->id)->delete();

        $name = $teacher->name;
        $teacher->delete();

        return redirect()->route('teachers.index')->with('success', "Teacher '{$name}' deleted successfully.");
    }
}
