<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use App\Models\TestResult;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentHistoryController extends Controller
{
    /**
     * Display the test history of a selected student across classes.
     */
    public function show(Request $request, Student $student): View
    {
        $student->load(['currentEnrollment.classSection.schoolClass', 'enrollments.classSection.schoolClass']);

        $query = TestResult::with([
            'test.classSectionSubject.classSection.schoolClass',
            'test.classSectionSubject.subject',
        ])
            ->where('student_id', $student->id);

        if ($subjectId = $request->input('subject_id')) {
            $query->whereHas('test.classSectionSubject', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereHas('test', function ($q) use ($dateFrom) {
                $q->where('test_date', '>=', $dateFrom);
            });
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereHas('test', function ($q) use ($dateTo) {
                $q->where('test_date', '<=', $dateTo);
            });
        }

        $results = $query->join('tests', 'test_results.test_id', '=', 'tests.id')
            ->select('test_results.*')
            ->orderBy('tests.test_date', 'desc')
            ->orderBy('tests.id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        // Calculate summary statistics
        $allResults = TestResult::where('student_id', $student->id)
            ->with('test')
            ->get();

        $totalTests = $allResults->count();
        $averagePercentage = 0;
        if ($totalTests > 0) {
            $percentages = $allResults->map(function ($r) {
                return $r->test->max_marks > 0 ? ($r->obtained_marks / $r->test->max_marks) * 100 : 0;
            });
            $averagePercentage = round($percentages->avg(), 1);
        }

        return view('students.history', compact('student', 'results', 'subjects', 'totalTests', 'averagePercentage'));
    }
}
