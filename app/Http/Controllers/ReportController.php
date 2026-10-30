<?php

namespace App\Http\Controllers;

use App\Models\Test;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Preview test report in browser (printable).
     */
    public function show(Request $request, Test $test): View
    {
        $test->load([
            'classSectionSubject.classSection.schoolClass',
            'classSectionSubject.subject',
            'creator',
            'results.student',
        ]);

        $results = $test->results->sortBy('student.name');

        $averageMarks = $results->count() > 0 ? round($results->avg('obtained_marks'), 1) : 0;
        $highestMarks = $results->count() > 0 ? $results->max('obtained_marks') : 0;
        $lowestMarks = $results->count() > 0 ? $results->min('obtained_marks') : 0;

        return view('reports.show', compact('test', 'results', 'averageMarks', 'highestMarks', 'lowestMarks'));
    }

    /**
     * Download the test report as a PDF file.
     */
    public function downloadPdf(Test $test): Response
    {
        $test->load([
            'classSectionSubject.classSection.schoolClass',
            'classSectionSubject.subject',
            'creator',
            'results.student',
        ]);

        $results = $test->results->sortBy('student.name');

        $averageMarks = $results->count() > 0 ? round($results->avg('obtained_marks'), 1) : 0;
        $highestMarks = $results->count() > 0 ? $results->max('obtained_marks') : 0;
        $lowestMarks = $results->count() > 0 ? $results->min('obtained_marks') : 0;

        $pdf = Pdf::loadView('reports.pdf', compact('test', 'results', 'averageMarks', 'highestMarks', 'lowestMarks'))
            ->setPaper('a4', 'portrait');

        $className = str_replace(' ', '_', $test->classSectionSubject->classSection->full_name);
        $subjectName = str_replace(' ', '_', $test->classSectionSubject->subject->name);
        $filename = "Test_Report_{$className}_{$subjectName}_{$test->test_date->format('Y-m-d')}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export test report results as CSV.
     */
    public function downloadCsv(Test $test): StreamedResponse
    {
        $test->load([
            'classSectionSubject.classSection.schoolClass',
            'classSectionSubject.subject',
            'creator',
            'results.student',
        ]);

        $results = $test->results->sortBy('student.name');

        $className = str_replace(' ', '_', $test->classSectionSubject->classSection->full_name);
        $subjectName = str_replace(' ', '_', $test->classSectionSubject->subject->name);
        $filename = "Test_Report_{$className}_{$subjectName}_{$test->test_date->format('Y-m-d')}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($test, $results) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // School & Test Header
            fputcsv($file, [config('app.name', 'School Daily Test Report System')]);
            fputcsv($file, ['Class:', $test->classSectionSubject->classSection->full_name]);
            fputcsv($file, ['Subject:', $test->classSectionSubject->subject->name]);
            fputcsv($file, ['Test Title:', $test->title]);
            fputcsv($file, ['Date:', $test->test_date->format('d M Y')]);
            fputcsv($file, ['Maximum Marks:', $test->max_marks]);
            fputcsv($file, ['Conducted By:', $test->creator->name]);
            fputcsv($file, []); // Blank separator

            // Table Header
            fputcsv($file, ['Sr.#', 'Student Name', 'B-Form Number', 'Obtained Marks', 'Max Marks', 'Percentage (%)']);

            $sr = 1;
            foreach ($results as $result) {
                fputcsv($file, [
                    $sr++,
                    $result->student->name,
                    $result->student->b_form_number,
                    $result->obtained_marks,
                    $test->max_marks,
                    $result->percentage.'%',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
