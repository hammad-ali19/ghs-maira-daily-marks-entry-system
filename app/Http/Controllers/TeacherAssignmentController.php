<?php

namespace App\Http\Controllers;

use App\Models\ClassSectionSubject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeacherAssignmentController extends Controller
{
    /**
     * Assign a teacher to a class section subject.
     * Authorized: Admin or the Class Incharge of this section.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_section_subject_id' => ['required', 'exists:class_section_subject,id'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $css = ClassSectionSubject::with('classSection')->findOrFail($validated['class_section_subject_id']);
        $user = $request->user();

        if (! $user->isAdmin() && $css->classSection->incharge_id !== $user->id) {
            abort(403, 'Only the administrator or the designated class incharge can assign teachers to this subject.');
        }

        $teacher = User::findOrFail($validated['user_id']);
        if (! $teacher->isTeacher() || ! $teacher->is_active) {
            return back()->with('error', 'Selected user must be an active teacher.');
        }

        $alreadyAssigned = TeacherAssignment::where('class_section_subject_id', $css->id)
            ->where('user_id', $teacher->id)
            ->first();

        if ($alreadyAssigned) {
            return back()->with('warning', " {$alreadyAssigned->teacher->name} is already teaching {$css->subject->name} to {$css->classSection->fullname}");
        }

        // Enforce: only one teacher per subject per section
        $existingAssignment = TeacherAssignment::with('teacher')
            ->where('class_section_subject_id', $css->id)
            ->first();

        if ($existingAssignment) {
            return back()->with('warning', "{$css->subject->name} is already being taught by MR. {$existingAssignment->teacher->name}. Remove the existing assignment first.");
        }

        TeacherAssignment::create([
            'class_section_subject_id' => $css->id,
            'user_id' => $teacher->id,
        ]);

        return redirect()->route('sections.show', $css->class_section_id)
            ->with('success', "Teacher {$teacher->name} assigned to subject successfully.");
    }

    /**
     * Remove teacher assignment.
     */
    public function destroy(Request $request, TeacherAssignment $assignment): RedirectResponse
    {
        $user = $request->user();
        $assignment->load('classSectionSubject.classSection');
        $section = $assignment->classSectionSubject->classSection;

        if (! $user->isAdmin() && $section->incharge_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $sectionId = $section->id;
        $assignment->delete();

        return redirect()->route('sections.show', $sectionId)
            ->with('success', 'Teacher assignment removed successfully.');
    }
}
