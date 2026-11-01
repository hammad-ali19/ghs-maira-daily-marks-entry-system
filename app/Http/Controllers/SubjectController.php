<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects.
     */
    public function index(): View
    {
        $subjects = Subject::withCount('classSectionSubjects')
            ->orderBy('name')
            ->paginate(15);

        return view('subjects.index', compact('subjects'));
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create(): View
    {
        return view('subjects.create');
    }

    /**
     * Store a newly created subject in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:subjects,name'],
        ]);

        $subject = Subject::create([
            'name' => ucwords(trim($validated['name'])),
            'is_active' => true,
        ]);

        return redirect()->route('subjects.index')->with('success', "Subject '{$subject->name}' added to catalogue successfully.");
    }

    /**
     * Show the form for editing the subject.
     */
    public function edit(Subject $subject): View
    {
        return view('subjects.edit', compact('subject'));
    }

    /**
     * Update the subject in storage.
     */
    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('subjects')->ignore($subject->id)],
            'is_active' => ['required', 'boolean'],
        ]);

        $subject->update([
            'name' => trim($validated['name']),
            'is_active' => (bool) $validated['is_active'],
        ]);

        return redirect()->route('subjects.index')->with('success', "Subject '{$subject->name}' updated successfully.");
    }

    /**
     * Remove the subject from storage safely.
     */
    public function destroy(Subject $subject): RedirectResponse
    {
        if ($subject->classSectionSubjects()->exists()) {
            return back()->with('error', "Subject '{$subject->name}' is assigned to class sections and cannot be deleted. Deactivate it instead.");
        }

        $subject->delete();

        return redirect()->route('subjects.index')->with('success', "Subject '{$subject->name}' deleted successfully.");
    }
}
