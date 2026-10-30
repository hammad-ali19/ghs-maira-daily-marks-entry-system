<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use App\Models\ClassSection;
use App\Models\ClassSectionSubject;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassSectionController extends Controller
{
  /**
   * Display the specified class section.
   */
  public function show(Request $request, ClassSection $section): View
  {
    $user = $request->user();

    $section->load([
      'schoolClass',
      'incharge',
      'currentEnrollments.student',
      'classSectionSubjects.subject',
      'classSectionSubjects.teacherAssignments.teacher',
    ]);

    $availableSubjects = Subject::where('is_active', true)
      ->whereNotIn('id', $section->classSectionSubjects->pluck('subject_id'))
      ->orderBy('name')
      ->get();

    $teachers = User::where('role', 'teacher')->where('is_active', true)->orderBy('name')->get();
    $inchargeAssignments = ClassSection::with('schoolClass')
      ->whereNotNull('incharge_id')
      ->where('id', '!=', $section->id)
      ->get()
      ->keyBy('incharge_id');

    $isAuthorizedIncharge = $user->isAdmin() || ($user->isTeacher() && $section->incharge_id === $user->id);

    return view('sections.show', compact('section', 'availableSubjects', 'teachers', 'isAuthorizedIncharge', 'inchargeAssignments'));
  }

  /**
   * Update incharge or active status (Admin only).
   */
  public function update(Request $request, ClassSection $section): RedirectResponse
  {
    if (! $request->user()->isAdmin()) {
      abort(403, 'Only administrators can update class incharge or section settings.');
    }

    $validated = $request->validate(
      [
        'name' => ['required', 'string', 'max:50'],
        'incharge_id' => ['nullable', 'exists:users,id'],
        'is_active' => ['required', 'boolean'],
      ],
      [
        'incharge_id.exists' => 'The selected incharge does not exist.',
      ]
    );

    $newInchargeId = $validated['incharge_id'] ?: null;

    if ($newInchargeId && $newInchargeId !== $section->incharge_id) {
      $existingInchargeSection = ClassSection::where('incharge_id', $newInchargeId)
        ->where('id', '!=', $section->id)
        ->first();

      if ($existingInchargeSection) {
        $existingInchargeSection->update(['incharge_id' => null]);
      }
    }

    $section->update([
      'name' => $validated['name'],
      'incharge_id' => $newInchargeId,
      'is_active' => (bool) $validated['is_active'],
    ]);

    return redirect()->route('sections.show', $section)->with('success', 'Section updated successfully.');
  }

  /**
   * Assign a subject to this class section.
   * Authorized: Admin or the Class Incharge.
   */
  public function assignSubject(Request $request, ClassSection $section): RedirectResponse
  {
    $user = $request->user();
    if (! $user->isAdmin() && $section->incharge_id !== $user->id) {
      abort(403, 'Only the administrator or the designated class incharge can assign subjects to this class.');
    }

    $validated = $request->validate([
      'subject_id' => ['required', 'exists:subjects,id'],
    ]);

    $exists = ClassSectionSubject::where('class_section_id', $section->id)
      ->where('subject_id', $validated['subject_id'])
      ->exists();

    if ($exists) {
      return back()->with('error', 'This subject is already assigned to this class section.');
    }

    ClassSectionSubject::create([
      'class_section_id' => $section->id,
      'subject_id' => $validated['subject_id'],
    ]);

    return redirect()->route('sections.show', $section)->with('success', 'Subject assigned to class section successfully.');
  }

  /**
   * Remove a subject from this class section.
   * Authorized: Admin or the Class Incharge.
   */
  public function removeSubject(Request $request, ClassSection $section, ClassSectionSubject $classSectionSubject): RedirectResponse
  {
    $user = $request->user();
    if (! $user->isAdmin() && $section->incharge_id !== $user->id) {
      abort(403, 'Only the administrator or the designated class incharge can remove subjects.');
    }

    if ($classSectionSubject->class_section_id !== $section->id) {
      abort(404);
    }

    // Check if tests have been recorded for this subject in this section
    if ($classSectionSubject->tests()->exists()) {
      return back()->with('error', 'Cannot remove this subject because tests have already been recorded for it. Historical data must be preserved.');
    }

    $classSectionSubject->teacherAssignments()->delete();
    $classSectionSubject->delete();

    return redirect()->route('sections.show', $section)->with('success', 'Subject removed from class section successfully.');
  }
}
