<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use App\Models\TestResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class StudentHistoryController extends Controller
{
    public const REPORT_TYPE_INDIVIDUAL = 'individual_tests';

    public const REPORT_TYPE_SUBJECT_AVERAGE = 'subject_average';

    /**
     * Display the test history of a selected student across classes.
     */
    public function show(Request $request, Student $student): View
    {
        $student->load(['currentEnrollment.classSection.schoolClass', 'enrollments.classSection.schoolClass']);
        // $classSectionId = $student->currentEnrollment->classSection->id;
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
                $q->whereDate('test_date', '>=', $dateFrom);
            });
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereHas('test', function ($q) use ($dateTo) {
                $q->whereDate('test_date', '<=', $dateTo);
            });
        }

        $results = $query->join('tests', 'test_results.test_id', '=', 'tests.id')
            ->select('test_results.*')
            ->orderBy('tests.test_date', 'desc')
            ->orderBy('tests.id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        // $subjects = Subject::where('is_active', true)
        //     ->when($classSectionId, function ($query) use ($classSectionId) {
        //         $query->whereHas('classSectionSubjects', function ($q) use ($classSectionId) {
        //             $q->where('class_section_id', $classSectionId);
        //         });
        //     })
        //     ->orderBy('name')
        //     ->get();

        // Calculate summary statistics
        $allResults = TestResult::where('student_id', $student->id)
            ->with('test')
            ->get();

        $totalTests = $allResults->count();
        $presentResults = $allResults->where('is_absent', false);
        $totalAbsents = $allResults->where('is_absent', true)->count();
        $testsAppeared = $presentResults->count();

        $averagePercentage = 0;
        if ($testsAppeared > 0) {
            $percentages = $presentResults->map(function ($r) {
                return $r->test->max_marks > 0 ? ($r->obtained_marks / $r->test->max_marks) * 100 : 0;
            });
            $averagePercentage = round($percentages->avg(), 1);
        }

        return view('students.history', compact('student', 'results', 'subjects', 'totalTests', 'totalAbsents', 'testsAppeared', 'averagePercentage'));
    }

    /**
     * Preview the student test history report.
     */
    public function report(Request $request, Student $student): View
    {
        $data = $this->resolveReportData($request, $student);

        return view('reports.student_history', $data);
    }

    /**
     * Download the student test history report as a PDF file.
     */
    public function downloadPdf(Request $request, Student $student): Response
    {
        $data = $this->resolveReportData($request, $student);

        $pdf = Pdf::loadView('reports.student_history_pdf', $data)
            ->setPaper('a4', 'portrait');

        $studentName = str_replace(' ', '_', $student->name);
        $filename = "Student_History_Report_{$studentName}_{now()->format('Y-m-d')}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Resolve and validate the report data shared by the preview and PDF responses.
     *
     * @return array<string, mixed>
     */
    private function resolveReportData(Request $request, Student $student): array
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'report_type' => ['nullable', 'string', 'in:individual_tests,subject_average'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
        ]);

        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;
        $reportType = $validated['report_type'] ?? self::REPORT_TYPE_INDIVIDUAL;
        $subjectId = $validated['subject_id'] ?? null;

        $student->load(['currentEnrollment.classSection.schoolClass']);

        $results = TestResult::with([
            'test.classSectionSubject.classSection.schoolClass',
            'test.classSectionSubject.subject',
        ])
            ->where('student_id', $student->id)
            ->when($subjectId, fn ($q) => $q->whereHas('test.classSectionSubject', function ($qq) use ($subjectId) {
                $qq->where('subject_id', $subjectId);
            }))
            ->when($dateFrom, fn ($q) => $q->whereHas('test', function ($qq) use ($dateFrom) {
                $qq->where('test_date', '>=', $dateFrom);
            }))
            ->when($dateTo, fn ($q) => $q->whereHas('test', function ($qq) use ($dateTo) {
                $qq->where('test_date', '<=', $dateTo);
            }))
            ->join('tests', 'test_results.test_id', '=', 'tests.id')
            ->select('test_results.*')
            ->orderBy('tests.test_date', 'desc')
            ->orderBy('tests.id', 'desc')
            ->get();

        $totalTests = $results->count();
        $presentResults = $results->where('is_absent', false);
        $totalAbsents = $results->where('is_absent', true)->count();
        $testsAppeared = $presentResults->count();

        $overallAverage = 0;
        if ($testsAppeared > 0) {
            $percentages = $presentResults->map(fn ($r) => $r->percentage)->filter(fn ($p) => $p !== null);
            $overallAverage = $percentages->count() > 0 ? round($percentages->avg(), 1) : 0;
        }

        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        $subjectSummary = null;
        if ($reportType === self::REPORT_TYPE_SUBJECT_AVERAGE) {
            $subjectSummary = $results->groupBy(fn ($r) => $r->test->classSectionSubject->subject_id)
                ->map(function ($subjectResults) {
                    $subject = $subjectResults->first()->test->classSectionSubject->subject;
                    $present = $subjectResults->where('is_absent', false);
                    $absentCount = $subjectResults->where('is_absent', true)->count();
                    $percentages = $present->map(fn ($r) => $r->percentage)->filter(fn ($p) => $p !== null);
                    $obtainedSum = $present->sum('obtained_marks');
                    $maxSum = $present->sum(fn ($r) => $r->test->max_marks);

                    return [
                        'subject' => $subject,
                        'results' => $subjectResults->sortByDesc(fn ($r) => $r->test->test_date),
                        'count' => $subjectResults->count(),
                        'appeared_count' => $present->count(),
                        'absent_count' => $absentCount,
                        'average_percentage' => $percentages->count() > 0 ? round($percentages->avg(), 1) : 0,
                        'average_marks' => $present->count() > 0 ? round($obtainedSum / $present->count(), 1) : 0,
                        'total_obtained' => $obtainedSum,
                        'total_max' => $maxSum,
                    ];
                })->values();
        }

        return compact(
            'student',
            'results',
            'reportType',
            'dateFrom',
            'dateTo',
            'subjectId',
            'subjects',
            'subjectSummary',
            'totalTests',
            'totalAbsents',
            'testsAppeared',
            'overallAverage'
        );
    }
}
