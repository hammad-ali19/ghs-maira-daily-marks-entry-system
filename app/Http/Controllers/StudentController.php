<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Display a listing of students with filters.
     */
    public function index(Request $request): View
    {
        $query = Student::with(['currentEnrollment.classSection.schoolClass']);

        if ($sectionId = $request->input('class_section_id')) {
            $query->whereHas('currentEnrollment', function ($q) use ($sectionId) {
                $q->where('class_section_id', $sectionId);
            });
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('b_form_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', (bool) $request->input('status'));
        }

        $students = $query->orderBy('name')->paginate(20)->withQueryString();

        $sections = ClassSection::with('schoolClass')->where('is_active', true)->get();

        return view('students.index', compact('students', 'sections'));
    }

    /**
     * Show the form for creating a new student.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        // If admin, can create in any class section
        // If teacher, only sections where they are incharge
        if ($user->isAdmin()) {
            $sections = ClassSection::with('schoolClass')->where('is_active', true)->get();
        } else {
            $sections = ClassSection::with('schoolClass')
                ->where('incharge_id', $user->id)
                ->where('is_active', true)
                ->get();

            if ($sections->isEmpty()) {
                abort(403, 'You are not assigned as a class incharge for any class section, so you cannot add students.');
            }
        }

        $preselectedSectionId = $request->input('class_section_id');

        return view('students.create', compact('sections', 'preselectedSectionId'));
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'b_form_number' => ['required', 'string', 'max:50', 'unique:students,b_form_number'],
            'class_section_id' => ['required', 'exists:class_sections,id'],
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $section = ClassSection::findOrFail($validated['class_section_id']);

        // Authorization check: Admin or class incharge of this section
        if (! $user->isAdmin() && $section->incharge_id !== $user->id) {
            abort(403, 'You can only add students to the class section for which you are the designated incharge.');
        }

        // Store photo in storage/app/public/students
        $photoPath = $request->file('photo')->store('students', 'public');

        DB::transaction(function () use ($validated, $photoPath, $section) {
            $student = Student::create([
                'name' => $validated['name'],
                'b_form_number' => $validated['b_form_number'],
                'photo_path' => $photoPath,
                'is_active' => true,
            ]);

            StudentEnrollment::create([
                'student_id' => $student->id,
                'class_section_id' => $section->id,
                'enrolled_at' => now()->toDateString(),
                'is_current' => true,
            ]);
        });

        return redirect()->route('students.index', ['class_section_id' => $section->id])
            ->with('success', "Student '{$validated['name']}' registered successfully.");
    }

    /**
     * Show the form for editing the student.
     */
    public function edit(Request $request, Student $student): View
    {
        $user = $request->user();
        $student->load('currentEnrollment.classSection');

        $currentSection = $student->currentEnrollment?->classSection;

        // Authorization check: Admin or incharge of current section
        if (! $user->isAdmin()) {
            if (! $currentSection || $currentSection->incharge_id !== $user->id) {
                abort(403, 'You are only permitted to edit students enrolled in the class you are incharge of.');
            }
            $sections = ClassSection::with('schoolClass')
                ->where('incharge_id', $user->id)
                ->where('is_active', true)
                ->get();
        } else {
            $sections = ClassSection::with('schoolClass')->where('is_active', true)->get();
        }

        return view('students.edit', compact('student', 'sections', 'currentSection'));
    }

    /**
     * Update the student in storage.
     */
    public function update(Request $request, Student $student): RedirectResponse
    {
        $user = $request->user();
        $student->load('currentEnrollment.classSection');
        $currentSection = $student->currentEnrollment?->classSection;

        if (! $user->isAdmin()) {
            if (! $currentSection || $currentSection->incharge_id !== $user->id) {
                abort(403, 'You are only permitted to update students enrolled in the class you are incharge of.');
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'b_form_number' => ['required', 'string', 'max:50', Rule::unique('students')->ignore($student->id)],
            'class_section_id' => ['required', 'exists:class_sections,id'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['required', 'boolean'],
        ]);

        $newSection = ClassSection::findOrFail($validated['class_section_id']);

        if (! $user->isAdmin() && $newSection->incharge_id !== $user->id) {
            abort(403, 'Cannot transfer student to a section you are not incharge of.');
        }

        DB::transaction(function () use ($student, $validated, $request, $currentSection, $newSection) {
            $data = [
                'name' => $validated['name'],
                'b_form_number' => $validated['b_form_number'],
                'is_active' => (bool) $validated['is_active'],
            ];

            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($student->photo_path && Storage::disk('public')->exists($student->photo_path)) {
                    Storage::disk('public')->delete($student->photo_path);
                }
                $data['photo_path'] = $request->file('photo')->store('students', 'public');
            }

            $student->update($data);

            // If class section changed, preserve enrollment history!
            if (! $currentSection || $currentSection->id !== $newSection->id) {
                // Mark previous current enrollment as left today
                StudentEnrollment::where('student_id', $student->id)
                    ->where('is_current', true)
                    ->update([
                        'is_current' => false,
                        'left_at' => now()->toDateString(),
                    ]);

                // Create new enrollment
                StudentEnrollment::create([
                    'student_id' => $student->id,
                    'class_section_id' => $newSection->id,
                    'enrolled_at' => now()->toDateString(),
                    'is_current' => true,
                ]);
            }
        });

        return redirect()->route('students.index')->with('success', "Student '{$student->name}' updated successfully.");
    }

    /**
     * Remove or deactivate a student safely.
     */
    public function destroy(Student $student): RedirectResponse
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can delete or deactivate students.');
        }

        // Check if student has historical test results
        if ($student->testResults()->exists()) {
            // Safely deactivate to preserve historical test records
            $student->update(['is_active' => false]);

            return redirect()->route('students.index')
                ->with('warning', "Student '{$student->name}' has recorded test marks. Deactivated instead of permanently deleted to preserve historical test records.");
        }

        // Delete photo if exists
        if ($student->photo_path && Storage::disk('public')->exists($student->photo_path)) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $student->enrollments()->delete();
        $student->delete();

        return redirect()->route('students.index')->with('success', "Student '{$student->name}' deleted successfully.");
    }
}
