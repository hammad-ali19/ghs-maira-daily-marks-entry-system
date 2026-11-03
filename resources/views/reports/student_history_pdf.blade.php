<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Student History Report - {{ $student->name }}</title>
    <style>
        @page {
            margin: 20px 25px;
        }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 10.5px;
            color: #1e293b;
            line-height: 1.35;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #14532d;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .header .subhead {
            font-size: 9px;
            font-weight: bold;
            color: #15803d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header h1 {
            font-size: 17px;
            margin: 3px 0;
            color: #0f172a;
            text-transform: uppercase;
        }
        .header .report-title {
            font-size: 10.5px;
            font-weight: bold;
            color: #334155;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 4px 6px;
            font-size: 10px;
            vertical-align: top;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            font-size: 8.5px;
        }
        .meta-value {
            font-weight: bold;
            color: #0f172a;
            font-size: 10.5px;
        }
        .criteria-box {
            width: 100%;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 14px;
            font-size: 9.5px;
            color: #334155;
        }
        .criteria-box .label {
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            font-size: 8px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .summary-table td {
            padding: 6px 8px;
            text-align: center;
            border: 1px solid #cbd5e1;
        }
        .summary-label {
            font-size: 8px;
            text-transform: uppercase;
            font-weight: bold;
            color: #64748b;
            display: block;
        }
        .summary-val {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .results-table th, .results-table td {
            border: 1px solid #94a3b8;
            padding: 5px 6px;
            text-align: left;
        }
        .results-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
            color: #0f172a;
        }
        .text-center {
            text-align: center !important;
        }
        .font-bold {
            font-weight: bold;
        }
        .subject-group {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }
        .subject-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .subject-header td {
            padding: 5px 8px;
            font-size: 10px;
            color: #0f172a;
            border-bottom: 2px solid #14532d;
            border-top: 1px solid #cbd5e1;
        }
        .subject-label {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
            color: #475569;
            display: block;
        }
        .signatures {
            width: 100%;
            margin-top: 40px;
        }
        .signatures td {
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            color: #334155;
            padding-top: 35px;
        }
        .sig-line {
            border-top: 1px solid #64748b;
            width: 120px;
            margin: 0 auto 4px auto;
        }
        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="subhead">Government of the Punjab &bull; School Education Department</div>
        <h1>{{ config('app.name', 'Government High School Maira') }}</h1>
        <div class="report-title">Student Academic Test History Report</div>
    </div>

    <table class="meta-table">
        <tr>
            <td width="33%">
                <div class="meta-label">Student Name:</div>
                <div class="meta-value">{{ $student->name }}</div>
            </td>
            <td width="33%">
                <div class="meta-label">B-Form / CNIC:</div>
                <div class="meta-value">{{ $student->b_form_number }}</div>
            </td>
            <td width="34%">
                <div class="meta-label">Current Class:</div>
                <div class="meta-value">{{ $student->currentEnrollment?->classSection->full_name ?? 'Not Enrolled' }}</div>
            </td>
        </tr>
    </table>

    <div class="criteria-box">
        <div class="label">Report Criteria</div>
        <div style="margin-top: 4px;">
            <span style="font-weight:bold;">Type:</span>
            {{ $reportType === 'subject_average' ? 'Average per Subject' : 'Individual Tests' }}
        </div>
        @if($dateFrom || $dateTo)
            <div style="margin-top: 3px;">
                <span style="font-weight:bold;">Date Range:</span>
                {{ $dateFrom ?? 'Any' }} to {{ $dateTo ?? 'Any' }}
            </div>
        @endif
        @if($subjectId)
            <div style="margin-top: 3px;">
                <span style="font-weight:bold;">Subject:</span>
                {{ optional($subjects->first(fn ($s) => $s->id == $subjectId))->name ?? $subjectId }}
            </div>
        @endif
    </div>

    <table class="summary-table">
        <tr>
            <td>
                <span class="summary-label">Total Tests</span>
                <span class="summary-val">{{ $totalTests }}</span>
            </td>
            <td>
                <span class="summary-label">Overall Average</span>
                <span class="summary-val" style="color: #15803d;">{{ $overallAverage }}%</span>
            </td>
            <td width="33%">
                <span class="summary-label">Student</span>
                <span class="summary-val">{{ $student->name }}</span>
            </td>
        </tr>
    </table>

    @if($totalTests === 0)
        <div style="text-align:center; padding: 40px; color: #64748b;">
            <div style="font-weight:bold; font-size:12px;">No test records found</div>
            <div style="font-size:9.5px;">This student does not have any recorded test marks matching the selected criteria.</div>
        </div>
    @else
        @if($reportType === 'subject_average')
            @foreach($subjectSummary as $summary)
                <div class="subject-group no-break">
                    @php
                        $sub = $summary['subject'];
                        $avgPct = $summary['average_percentage'];
                    @endphp
                    <table class="subject-header">
                        <tr>
                            <td width="70%">
                                <div class="subject-label">Subject</div>
                                <div style="font-weight:bold;">{{ $sub->name }}</div>
                            </td>
                            <td width="15%" class="text-center">
                                <div class="subject-label">Tests</div>
                                <div style="font-weight:bold;">{{ $summary['count'] }}</div>
                            </td>
                            <td width="15%" class="text-center">
                                <div class="subject-label">Avg %</div>
                                <div style="font-weight:bold; color: #15803d;">{{ $avgPct }}%</div>
                            </td>
                        </tr>
                    </table>
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th width="14%" class="text-center">Date</th>
                                <th width="26%">Class &amp; Section</th>
                                <th width="30%">Test Title</th>
                                <th width="10%" class="text-center">Obtained</th>
                                <th width="10%" class="text-center">Max</th>
                                <th width="10%" class="text-center">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($summary['results'] as $result)
                                @php
                                    $test = $result->test;
                                    $pct = $result->percentage;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $test->test_date->format('d M Y') }}</td>
                                    <td>{{ $test->classSectionSubject->classSection->full_name }}</td>
                                    <td>{{ $test->title }}</td>
                                    <td class="text-center font-bold">{{ $result->obtained_marks }}</td>
                                    <td class="text-center">{{ $test->max_marks }}</td>
                                    <td class="text-center">{{ $pct }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @else
            <table class="results-table">
                <thead>
                    <tr>
                        <th width="14%" class="text-center">Test Date</th>
                        <th width="22%">Class &amp; Section</th>
                        <th width="20%">Subject</th>
                        <th width="24%">Test Title</th>
                        <th width="10%" class="text-center">Obtained</th>
                        <th width="10%" class="text-center">Max Marks</th>
                        <th width="10%" class="text-center">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $result)
                        @php
                            $test = $result->test;
                            $pct = $result->percentage;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $test->test_date->format('d M Y') }}</td>
                            <td>{{ $test->classSectionSubject->classSection->full_name }}</td>
                            <td>{{ $test->classSectionSubject->subject->name }}</td>
                            <td>{{ $test->title }}</td>
                            <td class="text-center font-bold">{{ $result->obtained_marks }}</td>
                            <td class="text-center">{{ $test->max_marks }}</td>
                            <td class="text-center">{{ $pct }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <table class="signatures">
            <tr>
                <td width="33%">
                    <div class="sig-line"></div>
                    Subject Teacher
                </td>
                <td width="33%">
                    <div class="sig-line"></div>
                    Class Incharge
                </td>
                <td width="34%">
                    <div class="sig-line"></div>
                    Headmaster / Principal
                </td>
            </tr>
        </table>
    @endif
</body>
</html>
