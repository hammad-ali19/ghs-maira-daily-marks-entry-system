<?php

use App\Models\ClassSection;
use App\Models\ClassSectionSubject;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('teachers can view complete test history of any student across classes', function () {
    $teacher = User::factory()->teacher()->create();

    // Student was in 5th grade previously, now in 6th grade
    $student = Student::factory()->create(['name' => 'Usman Tariq']);

    $class5 = SchoolClass::factory()->create(['name' => '5th']);
    $sec5A = ClassSection::factory()->create(['school_class_id' => $class5->id, 'name' => 'A']);
    $subMath = Subject::factory()->create(['name' => 'Mathematics']);
    $css5 = ClassSectionSubject::create(['class_section_id' => $sec5A->id, 'subject_id' => $subMath->id]);

    $test5 = Test::create([
        'class_section_subject_id' => $css5->id,
        'title' => 'Grade 5 Fractions Quiz',
        'test_date' => '2025-05-10',
        'max_marks' => 20,
        'created_by' => $teacher->id,
    ]);
    TestResult::create(['test_id' => $test5->id, 'student_id' => $student->id, 'obtained_marks' => 18]);

    $class6 = SchoolClass::factory()->create(['name' => '6th']);
    $sec6A = ClassSection::factory()->create(['school_class_id' => $class6->id, 'name' => 'A']);
    $css6 = ClassSectionSubject::create(['class_section_id' => $sec6A->id, 'subject_id' => $subMath->id]);

    $test6 = Test::create([
        'class_section_subject_id' => $css6->id,
        'title' => 'Grade 6 Algebra Quiz',
        'test_date' => '2026-05-10',
        'max_marks' => 25,
        'created_by' => $teacher->id,
    ]);
    TestResult::create(['test_id' => $test6->id, 'student_id' => $student->id, 'obtained_marks' => 22]);

    StudentEnrollment::create(['student_id' => $student->id, 'class_section_id' => $sec6A->id, 'enrolled_at' => now(), 'is_current' => true]);

    $response = $this->actingAs($teacher)->get("/students/{$student->id}/history");

    $response->assertStatus(200)
        ->assertSee('Usman Tariq')
        ->assertSee('Grade 5 Fractions Quiz')
        ->assertSee('Grade 6 Algebra Quiz');
});

test('student test history can be filtered by subject and date range', function () {
    $teacher = User::factory()->teacher()->create();
    $student = Student::factory()->create();

    $class = SchoolClass::factory()->create();
    $section = ClassSection::factory()->create(['school_class_id' => $class->id]);

    $subUrdu = Subject::factory()->create(['name' => 'Urdu Literature']);
    $subEnglish = Subject::factory()->create(['name' => 'English Grammar']);

    $cssUrdu = ClassSectionSubject::create(['class_section_id' => $section->id, 'subject_id' => $subUrdu->id]);
    $cssEnglish = ClassSectionSubject::create(['class_section_id' => $section->id, 'subject_id' => $subEnglish->id]);

    $testUrdu = Test::create([
        'class_section_subject_id' => $cssUrdu->id,
        'title' => 'Urdu Nazm Test',
        'test_date' => '2026-09-01',
        'max_marks' => 20,
        'created_by' => $teacher->id,
    ]);
    TestResult::create(['test_id' => $testUrdu->id, 'student_id' => $student->id, 'obtained_marks' => 19]);

    $testEnglish = Test::create([
        'class_section_subject_id' => $cssEnglish->id,
        'title' => 'English Tenses Test',
        'test_date' => '2026-10-01',
        'max_marks' => 20,
        'created_by' => $teacher->id,
    ]);
    TestResult::create(['test_id' => $testEnglish->id, 'student_id' => $student->id, 'obtained_marks' => 15]);

    // Filter only Urdu
    $response = $this->actingAs($teacher)->get("/students/{$student->id}/history?subject_id={$subUrdu->id}");

    $response->assertStatus(200)
        ->assertSee('Urdu Nazm Test')
        ->assertDontSee('English Tenses Test');
});

test('user can preview printable subject test report', function () {
    $teacher = User::factory()->teacher()->create();
    $class = SchoolClass::factory()->create(['name' => '9th']);
    $section = ClassSection::factory()->create(['school_class_id' => $class->id, 'name' => 'B']);
    $subject = Subject::factory()->create(['name' => 'Physics']);
    $css = ClassSectionSubject::create(['class_section_id' => $section->id, 'subject_id' => $subject->id]);

    $test = Test::create([
        'class_section_subject_id' => $css->id,
        'title' => 'Kinematics Test',
        'test_date' => '2026-10-20',
        'max_marks' => 30,
        'created_by' => $teacher->id,
    ]);

    $student = Student::factory()->create(['name' => 'Zubair Shah', 'b_form_number' => '37405-3333333-3']);
    TestResult::create(['test_id' => $test->id, 'student_id' => $student->id, 'obtained_marks' => 27.5]);

    $response = $this->actingAs($teacher)->get("/reports/test/{$test->id}");

    $response->assertStatus(200)
        ->assertSee('Kinematics Test')
        ->assertSee('Zubair Shah')
        ->assertSee('37405-3333333-3')
        ->assertSee('27.5');
});

test('user can download report as PDF', function () {
    $teacher = User::factory()->teacher()->create();
    $class = SchoolClass::factory()->create();
    $section = ClassSection::factory()->create(['school_class_id' => $class->id]);
    $subject = Subject::factory()->create(['name' => 'Islamiat']);
    $css = ClassSectionSubject::create(['class_section_id' => $section->id, 'subject_id' => $subject->id]);

    $test = Test::create([
        'class_section_subject_id' => $css->id,
        'title' => 'Surah Baqarah Test',
        'test_date' => '2026-10-25',
        'max_marks' => 20,
        'created_by' => $teacher->id,
    ]);

    $student = Student::factory()->create();
    TestResult::create(['test_id' => $test->id, 'student_id' => $student->id, 'obtained_marks' => 19]);

    $response = $this->actingAs($teacher)->get("/reports/test/{$test->id}/pdf");

    $response->assertStatus(200)
        ->assertHeader('content-type', 'application/pdf');
});

test('user can export report as CSV', function () {
    $teacher = User::factory()->teacher()->create();
    $class = SchoolClass::factory()->create();
    $section = ClassSection::factory()->create(['school_class_id' => $class->id]);
    $subject = Subject::factory()->create(['name' => 'Chemistry']);
    $css = ClassSectionSubject::create(['class_section_id' => $section->id, 'subject_id' => $subject->id]);

    $test = Test::create([
        'class_section_subject_id' => $css->id,
        'title' => 'Periodic Table Test',
        'test_date' => '2026-10-25',
        'max_marks' => 25,
        'created_by' => $teacher->id,
    ]);

    $student = Student::factory()->create(['name' => 'Bilal Ahmed', 'b_form_number' => '37405-5555555-5']);
    TestResult::create(['test_id' => $test->id, 'student_id' => $student->id, 'obtained_marks' => 23]);

    $response = $this->actingAs($teacher)->get("/reports/test/{$test->id}/csv");

    $response->assertStatus(200)
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

test('admin can clear all test history for a student', function () {
    $admin = User::factory()->admin()->create();
    $student = Student::factory()->create(['name' => 'Prep Student one']);

    $class = SchoolClass::factory()->create();
    $section = ClassSection::factory()->create(['school_class_id' => $class->id]);
    $subject = Subject::factory()->create(['name' => 'Mathematics']);
    $css = ClassSectionSubject::create(['class_section_id' => $section->id, 'subject_id' => $subject->id]);

    $test = Test::create([
        'class_section_subject_id' => $css->id,
        'title' => 'Algebra Quiz',
        'test_date' => '2026-10-01',
        'max_marks' => 20,
        'created_by' => $admin->id,
    ]);
    TestResult::create(['test_id' => $test->id, 'student_id' => $student->id, 'obtained_marks' => 18]);

    $response = $this->actingAs($admin)
        ->delete(route('students.clear-history', $student));

    $response->assertRedirect(route('students.history', $student));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('test_results', ['student_id' => $student->id]);
    $this->assertFalse($student->fresh()->testResults()->exists());
});

test('student can be deleted after clearing test history', function () {
    $admin = User::factory()->admin()->create();
    $student = Student::factory()->create(['name' => 'Prep Student one']);

    $class = SchoolClass::factory()->create();
    $section = ClassSection::factory()->create(['school_class_id' => $class->id]);
    $subject = Subject::factory()->create(['name' => 'Mathematics']);
    $css = ClassSectionSubject::create(['class_section_id' => $section->id, 'subject_id' => $subject->id]);

    $test = Test::create([
        'class_section_subject_id' => $css->id,
        'title' => 'Algebra Quiz',
        'test_date' => '2026-10-01',
        'max_marks' => 20,
        'created_by' => $admin->id,
    ]);
    TestResult::create(['test_id' => $test->id, 'student_id' => $student->id, 'obtained_marks' => 18]);

    $this->assertTrue($student->fresh()->testResults()->exists());

    $this->actingAs($admin)->delete(route('students.clear-history', $student));

    $response = $this->actingAs($admin)->delete(route('students.destroy', $student));

    $response->assertRedirect(route('students.index'));
    $response->assertSessionHas('success');
    $this->assertNull($student->fresh());
});

test('teacher cannot clear test history for a student', function () {
    $teacher = User::factory()->teacher()->create();
    $student = Student::factory()->create();
    $class = SchoolClass::factory()->create();
    $section = ClassSection::factory()->create(['school_class_id' => $class->id]);
    $subject = Subject::factory()->create(['name' => 'Mathematics']);
    $css = ClassSectionSubject::create(['class_section_id' => $section->id, 'subject_id' => $subject->id]);

    $test = Test::create([
        'class_section_subject_id' => $css->id,
        'title' => 'Algebra Quiz',
        'test_date' => '2026-10-01',
        'max_marks' => 20,
        'created_by' => $teacher->id,
    ]);
    TestResult::create(['test_id' => $test->id, 'student_id' => $student->id, 'obtained_marks' => 18]);
    $response = $this->actingAs($teacher)
        ->delete(route('students.clear-history', $student));
    // dd($student->fresh()->testResults()->get());
    $response->assertStatus(403);
    $this->assertTrue($student->fresh()->testResults()->exists());
});
