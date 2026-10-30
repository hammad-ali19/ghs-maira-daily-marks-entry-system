<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\Test;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard according to the user's role.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $teacherCount = User::where('role', 'teacher')->where('is_active', true)->count();
            $studentCount = Student::where('is_active', true)->count();
            $sectionCount = ClassSection::where('is_active', true)->count();
            $subjectCount = Subject::where('is_active', true)->count();

            $recentTests = Test::with(['classSectionSubject.classSection.schoolClass', 'classSectionSubject.subject', 'creator'])
                ->latest('test_date')
                ->latest('id')
                ->take(10)
                ->get();

            return view('dashboard.admin', compact('teacherCount', 'studentCount', 'sectionCount', 'subjectCount', 'recentTests'));
        }

        // Teacher dashboard
        $inchargeSections = ClassSection::with(['schoolClass', 'currentEnrollments'])
            ->where('incharge_id', $user->id)
            ->where('is_active', true)
            ->get();

        $teachingAssignments = TeacherAssignment::with([
            'classSectionSubject.classSection.schoolClass',
            'classSectionSubject.subject',
        ])
            ->where('user_id', $user->id)
            ->get();

        // Recent tests created by or relevant to this teacher
        $inchargeSectionIds = $inchargeSections->pluck('id')->toArray();
        $assignedCssIds = $teachingAssignments->pluck('class_section_subject_id')->toArray();

        $recentTests = Test::with(['classSectionSubject.classSection.schoolClass', 'classSectionSubject.subject', 'creator'])
            ->where(function ($query) use ($user, $inchargeSectionIds, $assignedCssIds) {
                $query->where('created_by', $user->id)
                    ->orWhereIn('class_section_subject_id', $assignedCssIds)
                    ->orWhereHas('classSectionSubject', function ($q) use ($inchargeSectionIds) {
                        $q->whereIn('class_section_id', $inchargeSectionIds);
                    });
            })
            ->latest('test_date')
            ->latest('id')
            ->take(10)
            ->get();

        return view('dashboard.teacher', compact('inchargeSections', 'teachingAssignments', 'recentTests'));
    }
}
