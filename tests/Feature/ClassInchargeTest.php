<?php

use App\Models\ClassSection;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('class incharge can enroll a student in their class section with required photo', function () {
    Storage::fake('public');

    $incharge = User::factory()->teacher()->create();
    $class = SchoolClass::factory()->create(['name' => '5th']);
    $section = ClassSection::factory()->create([
        'school_class_id' => $class->id,
        'name' => 'Default',
        'incharge_id' => $incharge->id,
    ]);

    $file = UploadedFile::fake()->image('student_ali.jpg');

    $response = $this->actingAs($incharge)->post('/students', [
        'name' => 'Ali Raza',
        'b_form_number' => '37405-9988776-1',
        'class_section_id' => $section->id,
        'photo' => $file,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('students', [
        'name' => 'Ali Raza',
        'b_form_number' => '37405-9988776-1',
        'is_active' => true,
    ]);

    $student = Student::where('b_form_number', '37405-9988776-1')->first();
    expect($student)->not->toBeNull();
    Storage::disk('public')->assertExists($student->photo_path);

    $this->assertDatabaseHas('student_enrollments', [
        'student_id' => $student->id,
        'class_section_id' => $section->id,
        'is_current' => true,
    ]);
});

test('teacher who is not incharge cannot enroll student in that class section', function () {
    Storage::fake('public');

    $incharge = User::factory()->teacher()->create();
    $otherTeacher = User::factory()->teacher()->create();
    $class = SchoolClass::factory()->create();
    $section = ClassSection::factory()->create([
        'school_class_id' => $class->id,
        'incharge_id' => $incharge->id,
    ]);

    $file = UploadedFile::fake()->image('photo.jpg');

    $response = $this->actingAs($otherTeacher)->post('/students', [
        'name' => 'Unauthorized Student',
        'b_form_number' => '37405-1111111-1',
        'class_section_id' => $section->id,
        'photo' => $file,
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('students', ['b_form_number' => '37405-1111111-1']);
});

test('class incharge can assign subject to their class section', function () {
    $incharge = User::factory()->teacher()->create();
    $class = SchoolClass::factory()->create();
    $section = ClassSection::factory()->create([
        'school_class_id' => $class->id,
        'incharge_id' => $incharge->id,
    ]);
    $subject = Subject::factory()->create(['name' => 'Mathematics']);

    $response = $this->actingAs($incharge)->post("/sections/{$section->id}/subjects", [
        'subject_id' => $subject->id,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('class_section_subject', [
        'class_section_id' => $section->id,
        'subject_id' => $subject->id,
    ]);
});

test('teacher who is not incharge cannot assign subjects to that class section', function () {
    $incharge = User::factory()->teacher()->create();
    $otherTeacher = User::factory()->teacher()->create();
    $class = SchoolClass::factory()->create();
    $section = ClassSection::factory()->create([
        'school_class_id' => $class->id,
        'incharge_id' => $incharge->id,
    ]);
    $subject = Subject::factory()->create(['name' => 'Science']);

    $response = $this->actingAs($otherTeacher)->post("/sections/{$section->id}/subjects", [
        'subject_id' => $subject->id,
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('class_section_subject', [
        'class_section_id' => $section->id,
        'subject_id' => $subject->id,
    ]);
});

test('transferring a student to another section preserves enrollment history', function () {
    $admin = User::factory()->admin()->create();

    $class6 = SchoolClass::factory()->create(['name' => '6th']);
    $sec6A = ClassSection::factory()->create(['school_class_id' => $class6->id, 'name' => 'A']);

    $class7 = SchoolClass::factory()->create(['name' => '7th']);
    $sec7A = ClassSection::factory()->create(['school_class_id' => $class7->id, 'name' => 'A']);

    $student = Student::factory()->create(['name' => 'Kamran Khan']);
    $enrollment6 = StudentEnrollment::create([
        'student_id' => $student->id,
        'class_section_id' => $sec6A->id,
        'enrolled_at' => '2025-04-01',
        'is_current' => true,
    ]);

    // Admin promotes/transfers student to 7th A
    $response = $this->actingAs($admin)->put("/students/{$student->id}", [
        'name' => 'Kamran Khan',
        'b_form_number' => $student->b_form_number,
        'class_section_id' => $sec7A->id,
        'is_active' => true,
    ]);

    $response->assertRedirect();

    // Verify old enrollment is preserved with is_current = false and left_at set
    expect($enrollment6->fresh()->is_current)->toBeFalse();
    expect($enrollment6->fresh()->left_at)->not->toBeNull();

    // Verify new enrollment exists with is_current = true
    $this->assertDatabaseHas('student_enrollments', [
        'student_id' => $student->id,
        'class_section_id' => $sec7A->id,
        'is_current' => true,
    ]);
});
