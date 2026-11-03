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
        $averagePercentage = 0;
        if ($totalTests > 0) {
            $percentages = $allResults->map(function ($r) {
                return $r->test->max_marks > 0 ? ($r->obtained_marks / $r->test->max_marks) * 100 : 0;
            });
            $averagePercentage = round($percentages->avg(), 1);
        }
        // dd($student->currentEnrollment->classSection->id);
        return view('students.history', compact('student', 'results', 'subjects', 'totalTests', 'averagePercentage'));
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
        $overallAverage = 0;
        if ($totalTests > 0) {
            $overallAverage = round($results->map(fn ($r) => $r->percentage)->avg(), 1);
        }

        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        $subjectSummary = null;
        if ($reportType === self::REPORT_TYPE_SUBJECT_AVERAGE) {
            $subjectSummary = $results->groupBy(fn ($r) => $r->test->classSectionSubject->subject_id)
                ->map(function ($subjectResults) {
                    $subject = $subjectResults->first()->test->classSectionSubject->subject;
                    $percentages = $subjectResults->map(fn ($r) => $r->percentage);
                    $obtainedSum = $subjectResults->sum('obtained_marks');
                    $maxSum = $subjectResults->sum(fn ($r) => $r->test->max_marks);

                    return [
                        'subject' => $subject,
                        'results' => $subjectResults->sortByDesc(fn ($r) => $r->test->test_date),
                        'count' => $subjectResults->count(),
                        'average_percentage' => round($percentages->avg(), 1),
                        'average_marks' => $subjectResults->count() > 0 ? round($obtainedSum / $subjectResults->count(), 1) : 0,
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
            'overallAverage'
        );
    }
}
