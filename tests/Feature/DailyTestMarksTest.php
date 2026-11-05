<?php

use App\Models\ClassSection;
use App\Models\ClassSectionSubject;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('assigned subject teacher can create test and record bulk marks for students', function () {
    $teacher = User::factory()->teacher()->create();
    $class = SchoolClass::factory()->create(['name' => '8th']);
    $section = ClassSection::factory()->create(['school_class_id' => $class->id, 'name' => 'A']);
    $subject = Subject::factory()->create(['name' => 'Science']);

    $css = ClassSectionSubject::create([
        'class_section_id' => $section->id,
        'subject_id' => $subject->id,
    ]);

    TeacherAssignment::create([
        'user_id' => $teacher->id,
        'class_section_subject_id' => $css->id,
    ]);

    // Create 3 students in this section
    $student1 = Student::factory()->create();
    StudentEnrollment::create(['student_id' => $student1->id, 'class_section_id' => $section->id, 'enrolled_at' => now(), 'is_current' => true]);

    $student2 = Student::factory()->create();
    StudentEnrollment::create(['student_id' => $student2->id, 'class_section_id' => $section->id, 'enrolled_at' => now(), 'is_current' => true]);

    $response = $this->actingAs($teacher)->post('/tests', [
        'class_section_id' => $section->id,
        'class_section_subject_id' => $css->id,
        'title' => 'Biology Chapter 1 Quiz',
        'test_date' => '2026-10-15',
        'max_marks' => 20,
        'marks' => [
            ['student_id' => $student1->id, 'obtained' => 18.5],
            ['student_id' => $student2->id, 'obtained' => 14],
        ],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tests', [
        'class_section_subject_id' => $css->id,
        'title' => 'Biology Chapter 1 Quiz',
        'max_marks' => 20,
        'created_by' => $teacher->id,
    ]);

    $test = Test::where('title', 'Biology Chapter 1 Quiz')->first();
    expect($test)->not->toBeNull();

    $this->assertDatabaseHas('test_results', [
        'test_id' => $test->id,
        'student_id' => $student1->id,
        'obtained_marks' => 18.5,
    ]);

    $this->assertDatabaseHas('test_results', [
        'test_id' => $test->id,
        'student_id' => $student2->id,
        'obtained_marks' => 14.0,
    ]);
});

test('system rejects obtained marks that exceed maximum marks', function () {
    $teacher = User::factory()->teacher()->create();
    $class = SchoolClass::factory()->create();
    $section = ClassSection::factory()->create(['school_class_id' => $class->id, 'incharge_id' => $teacher->id]);
    $subject = Subject::factory()->create();
    $css = ClassSectionSubject::create(['class_section_id' => $section->id, 'subject_id' => $subject->id]);

    $student = Student::factory()->create();
    StudentEnrollment::create(['student_id' => $student->id, 'class_section_id' => $section->id, 'enrolled_at' => now(), 'is_current' => true]);

    $response = $this->actingAs($teacher)->post('/tests', [
        'class_section_id' => $section->id,
        'class_section_subject_id' => $css->id,
        'title' => 'Math Test',
        'test_date' => '2026-10-16',
        'max_marks' => 25,
        'marks' => [
            ['student_id' => $student->id, 'obtained' => 30], // Exceeds 25!
        ],
    ]);

    $response->assertSessionHasErrors();
    $this->assertDatabaseMissing('tests', ['title' => 'Math Test']);
});

test('system rejects negative obtained marks', function () {
    $teacher = User::factory()->teacher()->create();
    $class = SchoolClass::factory()->create();
    $section = ClassSection::factory()->create(['school_class_id' => $class->id, 'incharge_id' => $teacher->id]);
    $subject = Subject::factory()->create();
    $css = ClassSectionSubject::create(['class_section_id' => $section->id, 'subject_id' => $subject->id]);

    $student = Student::factory()->create();
    StudentEnrollment::create(['student_id' => $student->id, 'class_section_id' => $section->id, 'enrolled_at' => now(), 'is_current' => true]);

    $response = $this->actingAs($teacher)->post('/tests', [
        'class_section_id' => $section->id,
        'class_section_subject_id' => $css->id,
        'title' => 'Math Test',
        'test_date' => '2026-10-16',
        'max_marks' => 25,
        'marks' => [
            ['student_id' => $student->id, 'obtained' => -5],
        ],
    ]);

    $response->assertSessionHasErrors();
});

test('system prevents duplicate student marks in same test', function () {
    $teacher = User::factory()->teacher()->create();
    $class = SchoolClass::factory()->create();
    $section = ClassSection::factory()->create(['school_class_id' => $class->id, 'incharge_id' => $teacher->id]);
    $subject = Subject::factory()->create();
    $css = ClassSectionSubject::create(['class_section_id' => $section->id, 'subject_id' => $subject->id]);

    $student = Student::factory()->create();
    StudentEnrollment::create(['student_id' => $student->id, 'class_section_id' => $section->id, 'enrolled_at' => now(), 'is_current' => true]);

    $response = $this->actingAs($teacher)->post('/tests', [
        'class_section_id' => $section->id,
        'class_section_subject_id' => $css->id,
        'title' => 'Math Test Duplicate Check',
        'test_date' => '2026-10-16',
        'max_marks' => 25,
        'marks' => [
            ['student_id' => $student->id, 'obtained' => 15],
            ['student_id' => $student->id, 'obtained' => 20], // Duplicate student ID!
        ],
    ]);

    $response->assertSessionHasErrors();
    $this->assertDatabaseMissing('tests', ['title' => 'Math Test Duplicate Check']);
});

test('unauthorized teacher cannot record marks for non-assigned subject and section', function () {
    $teacher = User::factory()->teacher()->create();
    $class = SchoolClass::factory()->create();
    $section = ClassSection::factory()->create(['school_class_id' => $class->id]); // Not incharge
    $subject = Subject::factory()->create();
    $css = ClassSectionSubject::create(['class_section_id' => $section->id, 'subject_id' => $subject->id]); // Not assigned

    $student = Student::factory()->create();
    StudentEnrollment::create(['student_id' => $student->id, 'class_section_id' => $section->id, 'enrolled_at' => now(), 'is_current' => true]);

    $response = $this->actingAs($teacher)->post('/tests', [
        'class_section_id' => $section->id,
        'class_section_subject_id' => $css->id,
        'title' => 'Unauthorized Test Attempt',
        'test_date' => '2026-10-16',
        'max_marks' => 25,
        'marks' => [
            ['student_id' => $student->id, 'obtained' => 15],
        ],
    ]);

    $response->assertStatus(403);
});

test('authorized teacher can edit previously entered marks', function () {
    $teacher = User::factory()->teacher()->create();
    $class = SchoolClass::factory()->create();
    $section = ClassSection::factory()->create(['school_class_id' => $class->id, 'incharge_id' => $teacher->id]);
    $subject = Subject::factory()->create();
    $css = ClassSectionSubject::create(['class_section_id' => $section->id, 'subject_id' => $subject->id]);

    $student = Student::factory()->create();
    StudentEnrollment::create(['student_id' => $student->id, 'class_section_id' => $section->id, 'enrolled_at' => now(), 'is_current' => true]);

    $test = Test::create([
        'class_section_subject_id' => $css->id,
        'title' => 'Original Title',
        'test_date' => '2026-10-10',
        'max_marks' => 20,
        'created_by' => $teacher->id,
    ]);

    $result = TestResult::create([
        'test_id' => $test->id,
        'student_id' => $student->id,
        'obtained_marks' => 12,
    ]);

    $response = $this->actingAs($teacher)->put("/tests/{$test->id}", [
        'title' => 'Updated Title',
        'test_date' => '2026-10-11',
        'max_marks' => 20,
        'marks' => [
            ['student_id' => $student->id, 'obtained' => 17.5],
        ],
    ]);

    $response->assertRedirect();
    expect($test->fresh()->title)->toBe('Updated Title');
    expect((float) $result->fresh()->obtained_marks)->toBe(17.5);
});

test('teacher can mark a student as absent when recording test marks', function () {
    $teacher = User::factory()->teacher()->create();
    $class = SchoolClass::factory()->create();
    $section = ClassSection::factory()->create(['school_class_id' => $class->id, 'incharge_id' => $teacher->id]);
    $subject = Subject::factory()->create();
    $css = ClassSectionSubject::create(['class_section_id' => $section->id, 'subject_id' => $subject->id]);

    $student1 = Student::factory()->create();
    StudentEnrollment::create(['student_id' => $student1->id, 'class_section_id' => $section->id, 'enrolled_at' => now(), 'is_current' => true]);

    $student2 = Student::factory()->create();
    StudentEnrollment::create(['student_id' => $student2->id, 'class_section_id' => $section->id, 'enrolled_at' => now(), 'is_current' => true]);

    $response = $this->actingAs($teacher)->post('/tests', [
        'class_section_id' => $section->id,
        'class_section_subject_id' => $css->id,
        'title' => 'Weekly Quiz with Absent Student',
        'test_date' => '2026-10-20',
        'max_marks' => 20,
        'marks' => [
            ['student_id' => $student1->id, 'obtained' => 18, 'is_absent' => 0],
            ['student_id' => $student2->id, 'obtained' => '', 'is_absent' => 1],
        ],
    ]);

    $response->assertRedirect();
    $test = Test::where('title', 'Weekly Quiz with Absent Student')->first();
    expect($test)->not->toBeNull();

    $this->assertDatabaseHas('test_results', [
        'test_id' => $test->id,
        'student_id' => $student1->id,
        'is_absent' => false,
        'obtained_marks' => 18.0,
    ]);

    $this->assertDatabaseHas('test_results', [
        'test_id' => $test->id,
        'student_id' => $student2->id,
        'is_absent' => true,
        'obtained_marks' => null,
    ]);

    // View show page and ensure Absent badge and stats are displayed
    $showResponse = $this->actingAs($teacher)->get("/tests/{$test->id}");
    $showResponse->assertStatus(200)
        ->assertSee('Absent')
        ->assertSee('Present');
});

test('teacher can update an existing test result to mark a student absent or present', function () {
    $teacher = User::factory()->teacher()->create();
    $class = SchoolClass::factory()->create();
    $section = ClassSection::factory()->create(['school_class_id' => $class->id, 'incharge_id' => $teacher->id]);
    $subject = Subject::factory()->create();
    $css = ClassSectionSubject::create(['class_section_id' => $section->id, 'subject_id' => $subject->id]);

    $student = Student::factory()->create();
    StudentEnrollment::create(['student_id' => $student->id, 'class_section_id' => $section->id, 'enrolled_at' => now(), 'is_current' => true]);

    $test = Test::create([
        'class_section_subject_id' => $css->id,
        'title' => 'Test To Update Attendance',
        'test_date' => '2026-10-20',
        'max_marks' => 20,
        'created_by' => $teacher->id,
    ]);

    $result = TestResult::create([
        'test_id' => $test->id,
        'student_id' => $student->id,
        'is_absent' => false,
        'obtained_marks' => 15,
    ]);

    // Mark student absent
    $response = $this->actingAs($teacher)->put("/tests/{$test->id}", [
        'title' => 'Test To Update Attendance',
        'test_date' => '2026-10-20',
        'max_marks' => 20,
        'marks' => [
            ['student_id' => $student->id, 'obtained' => '', 'is_absent' => 1],
        ],
    ]);

    $response->assertRedirect();
    $freshResult = $result->fresh();
    expect($freshResult->is_absent)->toBeTrue();
    expect($freshResult->obtained_marks)->toBeNull();

    // Mark student present again
    $response2 = $this->actingAs($teacher)->put("/tests/{$test->id}", [
        'title' => 'Test To Update Attendance',
        'test_date' => '2026-10-20',
        'max_marks' => 20,
        'marks' => [
            ['student_id' => $student->id, 'obtained' => 19, 'is_absent' => 0],
        ],
    ]);

    $response2->assertRedirect();
    $freshResult2 = $result->fresh();
    expect($freshResult2->is_absent)->toBeFalse();
    expect((float) $freshResult2->obtained_marks)->toBe(19.0);
});
