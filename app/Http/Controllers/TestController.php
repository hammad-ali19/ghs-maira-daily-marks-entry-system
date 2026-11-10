<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\ClassSectionSubject;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TestController extends Controller
{
    /**
     * Display a listing of tests with filters.
     */
    public function index(Request $request): View
    {
        $query = Test::with([
            'classSectionSubject.classSection.schoolClass',
            'classSectionSubject.subject',
            'creator',
            'results',
        ]);

        if ($sectionId = $request->input('class_section_id')) {
            $query->whereHas('classSectionSubject', function ($q) use ($sectionId) {
                $q->where('class_section_id', $sectionId);
            });
        }

        if ($subjectId = $request->input('subject_id')) {
            $query->whereHas('classSectionSubject', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('test_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('test_date', '<=', $dateTo);
        }

        $tests = $query->latest('test_date')->latest('id')->paginate(15)->withQueryString();

        $sections = ClassSection::with('schoolClass')->where('is_active', true)->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        return view('tests.index', compact('tests', 'sections', 'subjects'));
    }

    /**
     * Show the form for creating a new test and entering marks.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        // Find available sections:
        // Admin: all sections
        // Teacher: sections where they are incharge OR assigned to teach at least one subject
        if ($user->isAdmin()) {
            $sections = ClassSection::with('schoolClass')->where('is_active', true)->get();
        } else {
            $sections = ClassSection::with('schoolClass')
                ->where('is_active', true)
                ->where(function ($q) use ($user) {
                    $q->where('incharge_id', $user->id)
                        ->orWhereHas('classSectionSubjects.teacherAssignments', function ($aq) use ($user) {
                            $aq->where('user_id', $user->id);
                        });
                })
                ->get();
        }

        $selectedSectionId = $request->input('class_section_id');
        $selectedSubjectId = $request->input('subject_id');

        $isAuthorized = false; // Initialize it

        $availableSubjects = collect();
        $students = collect();

        if ($selectedSectionId) {
            $selectedSection = ClassSection::find($selectedSectionId);

            if ($selectedSection) {
                // If teacher, only show subjects they are assigned to
                $cssQuery = ClassSectionSubject::with('subject')
                    ->where('class_section_id', $selectedSectionId);

                if (! $user->isAdmin()) {     //  && $selectedSection->incharge_id !== $user->id
                    $cssQuery->whereHas('teacherAssignments', function ($tq) use ($user) {
                        $tq->where('user_id', $user->id);
                    });
                }

                $availableSubjects = $cssQuery->get()->map(function ($css) {
                    return (object) [
                        'class_section_subject_id' => $css->id,
                        'subject_id' => $css->subject_id,
                        'name' => $css->subject->name,
                    ];
                });

                // Get active enrolled students in this section
                $students = Student::where('is_active', true)
                    ->whereHas('currentEnrollment', function ($eq) use ($selectedSectionId) {
                        $eq->where('class_section_id', $selectedSectionId);
                    })
                    ->orderBy('name')
                    ->get();
            }
            if ($selectedSection) {
                $isAuthorized = $selectedSection->incharge_id === $user->id || $user->isAdmin();
            }
        }

        return view('tests.create', compact('isAuthorized', 'sections', 'selectedSectionId', 'selectedSubjectId', 'availableSubjects', 'students'));
    }

    /**
     * Store a newly created test and marks in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'class_section_id' => ['required', 'exists:class_sections,id'],
            'class_section_subject_id' => ['required', 'exists:class_section_subject,id'],
            'title' => ['required', 'string', 'max:255'],
            'test_date' => ['required', 'date'],
            'max_marks' => ['required', 'integer', 'min:1', 'max:1000'],
            'marks' => ['required', 'array', 'min:1'],
            'marks.*.student_id' => ['required', 'exists:students,id'],
            'marks.*.is_absent' => ['nullable', 'boolean'],
            'marks.*.obtained' => ['nullable', 'numeric', 'min:0'],
        ]);

        $section = ClassSection::findOrFail($validated['class_section_id']);
        $css = ClassSectionSubject::findOrFail($validated['class_section_subject_id']);

        if ($css->class_section_id !== $section->id) {
            throw ValidationException::withMessages([
                'class_section_subject_id' => 'The selected subject does not belong to the selected class section.',
            ]);
        }

        // Authorization check:
        // Admin, or Class Incharge of this section, or Teacher assigned to this class_section_subject
        $isAuthorized = $user->isAdmin()
            || ($section->incharge_id === $user->id)
            || $user->isAssignedTo($css);

        if (! $isAuthorized) {
            abort(403, 'You are not authorized to record marks for this class and subject.');
        }

        $maxMarks = (float) $validated['max_marks'];

        // Get currently enrolled student IDs in this section
        $enrolledStudentIds = StudentEnrollment::where('class_section_id', $section->id)
            ->where('is_current', true)
            ->pluck('student_id')
            ->toArray();

        // Validate obtained marks against max_marks and verify enrollment
        $submittedStudentIds = [];
        foreach ($validated['marks'] as $index => $markData) {
            $studentId = (int) $markData['student_id'];
            $isAbsent = filter_var($markData['is_absent'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if (! in_array($studentId, $enrolledStudentIds)) {
                throw ValidationException::withMessages([
                    "marks.{$index}.obtained" => 'One or more students are not currently enrolled in this class section.',
                ]);
            }

            if (in_array($studentId, $submittedStudentIds)) {
                throw ValidationException::withMessages([
                    "marks.{$index}.student_id" => 'Duplicate marks entry detected for a student in this test.',
                ]);
            }

            if (! $isAbsent) {
                if (! isset($markData['obtained']) || $markData['obtained'] === null || $markData['obtained'] === '') {
                    throw ValidationException::withMessages([
                        "marks.{$index}.obtained" => 'Obtained marks are required unless the student is marked as absent.',
                    ]);
                }

                $obtained = (float) $markData['obtained'];

                if ($obtained > $maxMarks) {
                    throw ValidationException::withMessages([
                        "marks.{$index}.obtained" => "Obtained marks ({$obtained}) cannot exceed maximum marks ({$maxMarks}).",
                    ]);
                }
            }

            $submittedStudentIds[] = $studentId;
        }

        $test = DB::transaction(function () use ($validated, $css, $user) {
            $newTest = Test::create([
                'class_section_subject_id' => $css->id,
                'title' => $validated['title'],
                'test_date' => $validated['test_date'],
                'max_marks' => $validated['max_marks'],
                'created_by' => $user->id,
            ]);

            // 1. Prepare an array to hold all records
            $records = [];
            $now = now(); // Ensure timestamps are identical for the batch

            foreach ($validated['marks'] as $markData) {
                $isAbsent = filter_var($markData['is_absent'] ?? false, FILTER_VALIDATE_BOOLEAN);

                $records[] = [
                    'test_id'        => $newTest->id,
                    'student_id'     => $markData['student_id'],
                    'is_absent'      => $isAbsent,
                    'obtained_marks' => $isAbsent ? null : $markData['obtained'],
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }

            // 2. Insert everything in a single query
            TestResult::insert($records);

            // foreach ($validated['marks'] as $markData) {
            //     $isAbsent = filter_var($markData['is_absent'] ?? false, FILTER_VALIDATE_BOOLEAN);

            //     TestResult::create([
            //         'test_id' => $newTest->id,
            //         'student_id' => $markData['student_id'],
            //         'is_absent' => $isAbsent,
            //         'obtained_marks' => $isAbsent ? null : $markData['obtained'],
            //     ]);
            // }

            return $newTest;
        });

        return redirect()->route('tests.show', $test)->with('success', 'Daily test marks recorded successfully.');
    }

    /**
     * Display the specified test and results.
     */
    public function show(Test $test): View
    {
        $test->load([
            'classSectionSubject.classSection.schoolClass',
            'classSectionSubject.subject',
            'creator',
            'results.student',
        ]);

        $results = $test->results->sortBy('student.name');

        $presentResults = $results->where('is_absent', false);
        $averageMarks = $presentResults->count() > 0 ? round($presentResults->avg('obtained_marks'), 1) : 0;
        $highestMarks = $presentResults->count() > 0 ? $presentResults->max('obtained_marks') : 0;
        $lowestMarks = $presentResults->count() > 0 ? $presentResults->min('obtained_marks') : 0;
        $presentCount = $presentResults->count();
        $absentCount = $results->where('is_absent', true)->count();

        /** @var User $user */
        $user = Auth::user();
        // $section = $test->classSectionSubject->classSection;
        // $canEdit = $user->isAdmin() || $user->isAssignedTo($test->classSectionSubject);
        $canEdit = $user->isAdmin() || $user->id === $test->creator->id;

        // dump($canEdit);
        return view('tests.show', compact('test', 'results', 'averageMarks', 'highestMarks', 'lowestMarks', 'canEdit', 'presentCount', 'absentCount'));
    }

    /**
     * Show the form for editing the test and marks.
     */
    public function edit(Test $test): View
    {
        /** @var User $user */
        $user = Auth::user();
        $test->load([
            'classSectionSubject.classSection.schoolClass',
            'classSectionSubject.subject',
            'results.student',
        ]);

        $section = $test->classSectionSubject->classSection;
        $isAuthorized = $user->isAdmin()
            || ($section->incharge_id === $user->id)
            || $user->isAssignedTo($test->classSectionSubject);

        if (! $isAuthorized) {
            abort(403, 'You are not authorized to edit this test.');
        }

        $results = $test->results->sortBy('student.name');

        return view('tests.edit', compact('test', 'results'));
    }

    /**
     * Update the test and marks in storage.
     */
    public function update(Request $request, Test $test): RedirectResponse
    {
        $user = $request->user();
        $test->load('classSectionSubject.classSection');
        $section = $test->classSectionSubject->classSection;

        $isAuthorized = $user->isAdmin()
            || ($section->incharge_id === $user->id)
            || $user->isAssignedTo($test->classSectionSubject);

        if (! $isAuthorized) {
            abort(403, 'You are not authorized to update this test.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'test_date' => ['required', 'date'],
            'max_marks' => ['required', 'integer', 'min:1', 'max:1000'],
            'marks' => ['required', 'array', 'min:1'],
            'marks.*.student_id' => ['required', 'exists:students,id'],
            'marks.*.is_absent' => ['nullable', 'boolean'],
            'marks.*.obtained' => ['nullable', 'numeric', 'min:0'],
        ]);

        $maxMarks = (float) $validated['max_marks'];

        // Validate marks
        $seenStudentIds = [];
        foreach ($validated['marks'] as $index => $markData) {
            $studentId = (int) $markData['student_id'];
            $isAbsent = filter_var($markData['is_absent'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if (in_array($studentId, $seenStudentIds)) {
                throw ValidationException::withMessages([
                    "marks.{$index}.student_id" => 'Duplicate student result in submission.',
                ]);
            }

            if (! $isAbsent) {
                if (! isset($markData['obtained']) || $markData['obtained'] === null || $markData['obtained'] === '') {
                    throw ValidationException::withMessages([
                        "marks.{$index}.obtained" => 'Obtained marks are required unless the student is marked as absent.',
                    ]);
                }

                $obtained = (float) $markData['obtained'];

                if ($obtained > $maxMarks) {
                    throw ValidationException::withMessages([
                        "marks.{$index}.obtained" => "Obtained marks ({$obtained}) cannot exceed maximum marks ({$maxMarks}).",
                    ]);
                }
            }

            $seenStudentIds[] = $studentId;
        }

        DB::transaction(function () use ($test, $validated) {
            $test->update([
                'title' => $validated['title'],
                'test_date' => $validated['test_date'],
                'max_marks' => $validated['max_marks'],
            ]);

            foreach ($validated['marks'] as $markData) {
                $isAbsent = filter_var($markData['is_absent'] ?? false, FILTER_VALIDATE_BOOLEAN);

                TestResult::updateOrCreate(
                    [
                        'test_id' => $test->id,
                        'student_id' => $markData['student_id'],
                    ],
                    [
                        'is_absent' => $isAbsent,
                        'obtained_marks' => $isAbsent ? null : $markData['obtained'],
                    ]
                );
            }
        });

        return redirect()->route('tests.show', $test)->with('success', 'Test marks updated successfully.');
    }

    /**
     * Safely delete a test (Admin only).
     */
    public function destroy(Test $test): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if (! $user->isAdmin()) {
            abort(403, 'Only administrators can delete test records.');
        }

        DB::transaction(function () use ($test) {
            $test->results()->delete();
            $test->delete();
        });

        return redirect()->route('tests.index')->with('success', 'Test record deleted successfully.');
    }
}
