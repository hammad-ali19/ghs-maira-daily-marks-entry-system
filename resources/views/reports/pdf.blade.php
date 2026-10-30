<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Test Report - {{ $test->title }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.3;
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
            font-size: 18px;
            margin: 3px 0;
            color: #0f172a;
            text-transform: uppercase;
        }
        .header .report-title {
            font-size: 11px;
            font-weight: bold;
            color: #334155;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 4px 6px;
            font-size: 10px;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            font-size: 9px;
        }
        .meta-value {
            font-weight: bold;
            color: #0f172a;
            font-size: 11px;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .results-table th, .results-table td {
            border: 1px solid #94a3b8;
            padding: 5px 6px;
            text-align: left;
        }
        .results-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            color: #0f172a;
        }
        .text-center {
            text-align: center !important;
        }
        .font-bold {
            font-weight: bold;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            margin-bottom: 30px;
        }
        .stats-table td {
            padding: 6px;
            text-align: center;
        }
        .stats-label {
            font-size: 8px;
            text-transform: uppercase;
            font-weight: bold;
            color: #64748b;
            display: block;
        }
        .stats-val {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
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
    </style>
</head>
<body>
    <div class="header">
        <div class="subhead">Government of the Punjab &bull; School Education Department</div>
        <h1>{{ config('app.name', 'Government High School Maira') }}</h1>
        <div class="report-title">Daily Subject Test Assessment Report</div>
    </div>

    <table class="meta-table">
        <tr>
            <td width="33%">
                <div class="meta-label">Class & Section:</div>
                <div class="meta-value">{{ $test->classSectionSubject->classSection->full_name }}</div>
            </td>
            <td width="33%">
                <div class="meta-label">Subject:</div>
                <div class="meta-value">{{ $test->classSectionSubject->subject->name }}</div>
            </td>
            <td width="34%">
                <div class="meta-label">Date Conducted:</div>
                <div class="meta-value">{{ $test->test_date->format('d M Y') }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="meta-label">Test Title:</div>
                <div class="meta-value">{{ $test->title }}</div>
            </td>
            <td>
                <div class="meta-label">Maximum Marks:</div>
                <div class="meta-value">{{ $test->max_marks }}</div>
            </td>
            <td>
                <div class="meta-label">Conducted By:</div>
                <div class="meta-value">{{ $test->creator->name }}</div>
            </td>
        </tr>
    </table>

    <table class="results-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">Sr.</th>
                <th width="35%">Student Full Name</th>
                <th width="26%">B-Form / Admission Number</th>
                <th width="12%" class="text-center">Obtained</th>
                <th width="10%" class="text-center">Max</th>
                <th width="12%" class="text-center">Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $result)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="font-bold">{{ $result->student->name }}</td>
                    <td>{{ $result->student->b_form_number }}</td>
                    <td class="text-center font-bold">{{ $result->obtained_marks }}</td>
                    <td class="text-center">{{ $test->max_marks }}</td>
                    <td class="text-center font-bold">{{ $result->percentage }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="stats-table">
        <tr>
            <td>
                <span class="stats-label">Total Tested</span>
                <span class="stats-val">{{ $results->count() }}</span>
            </td>
            <td>
                <span class="stats-label">Class Average</span>
                <span class="stats-val" style="color: #15803d;">{{ $averageMarks }} / {{ $test->max_marks }}</span>
            </td>
            <td>
                <span class="stats-label">Highest Score</span>
                <span class="stats-val">{{ $highestMarks }}</span>
            </td>
            <td>
                <span class="stats-label">Lowest Score</span>
                <span class="stats-val">{{ $lowestMarks }}</span>
            </td>
        </tr>
    </table>

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
</body>
</html>
