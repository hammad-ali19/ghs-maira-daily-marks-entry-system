<?php

use App\Models\ClassSection;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can view teacher management page', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/teachers');

    $response->assertStatus(200)
        ->assertSee('Teacher Accounts Management');
});

test('teacher cannot access teacher management page', function () {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->get('/teachers');

    $response->assertStatus(403);
});

test('admin can create teacher account with credentials', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/teachers', [
        'name' => 'Tariq Mehmood',
        'email' => 'tariq@ghsmaira.edu.pk',
        'password' => 'SecurePass123!',
    ]);

    $response->assertRedirect('/teachers');
    $this->assertDatabaseHas('users', [
        'name' => 'Tariq Mehmood',
        'email' => 'tariq@ghsmaira.edu.pk',
        'role' => 'teacher',
        'is_active' => true,
    ]);
});

test('admin can toggle teacher active status', function () {
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create(['is_active' => true]);

    $response = $this->actingAs($admin)->patch("/teachers/{$teacher->id}/toggle");

    $response->assertSessionHas('success');
    expect($teacher->fresh()->is_active)->toBeFalse();
});

test('admin can create school class and sections dynamically', function () {
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($admin)->post('/classes', [
        'name' => '6th',
        'display_order' => 6,
        'sections' => [
            ['name' => 'A', 'incharge_id' => $teacher->id],
            ['name' => 'B', 'incharge_id' => null],
        ],
    ]);

    $response->assertRedirect('/classes');
    $this->assertDatabaseHas('school_classes', ['name' => '6th']);
    $this->assertDatabaseHas('class_sections', ['name' => 'A', 'incharge_id' => $teacher->id]);
    $this->assertDatabaseHas('class_sections', ['name' => 'B', 'incharge_id' => null]);
});

test('admin can change class incharge', function () {
    $admin = User::factory()->admin()->create();
    $teacher1 = User::factory()->teacher()->create();
    $teacher2 = User::factory()->teacher()->create();

    $class = SchoolClass::factory()->create(['name' => '7th']);
    $section = ClassSection::factory()->create([
        'school_class_id' => $class->id,
        'name' => 'A',
        'incharge_id' => $teacher1->id,
    ]);

    $response = $this->actingAs($admin)->patch("/sections/{$section->id}", [
        'name' => 'A',
        'incharge_id' => $teacher2->id,
        'is_active' => true,
    ]);

    $response->assertRedirect();
    expect($section->fresh()->incharge_id)->toBe($teacher2->id);
});

test('admin can manage global subject catalogue', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/subjects', [
        'name' => 'Computer Science',
    ]);

    $response->assertRedirect('/subjects');
    $this->assertDatabaseHas('subjects', ['name' => 'Computer Science', 'is_active' => true]);
});
