<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClassSectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentHistoryController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherAssignmentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm']);
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Application Routes
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin-only management routes
    Route::middleware('role:admin')->group(function () {
        // Teacher Account Management
        Route::resource('teachers', TeacherController::class)->except(['show', 'destroy']);
        Route::patch('/teachers/{teacher}/toggle', [TeacherController::class, 'toggleStatus'])->name('teachers.toggle');

        // School Classes & Sections Management
        Route::resource('classes', SchoolClassController::class)->except(['show']);
        Route::patch('/sections/{section}', [ClassSectionController::class, 'update'])->name('sections.update');

        // Subject Catalogue Management
        Route::resource('subjects', SubjectController::class)->except(['show']);

        // Destructive / Deletion operations restricted to admin
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
        Route::delete('/tests/{test}', [TestController::class, 'destroy'])->name('tests.destroy');
    });

    // Class & Section view routes (accessible to both admin and teachers)
    Route::get('/classes/{class}', [SchoolClassController::class, 'show'])->name('classes.show');
    Route::get('/sections/{section}', [ClassSectionController::class, 'show'])->name('sections.show');

    // Section Subject & Teacher Assignment routes (Admin or Class Incharge authorized in controller)
    Route::post('/sections/{section}/subjects', [ClassSectionController::class, 'assignSubject'])->name('sections.subjects.assign');
    Route::delete('/sections/{section}/subjects/{classSectionSubject}', [ClassSectionController::class, 'removeSubject'])->name('sections.subjects.remove');
    Route::post('/teacher-assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');
    Route::delete('/teacher-assignments/{assignment}', [TeacherAssignmentController::class, 'destroy'])->name('assignments.destroy');

    // Student Management (Admin or authorized Class Incharge)
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');

    // Student Test History (viewable by all teachers and admin across the school)
    Route::get('/students/{student}/history', [StudentHistoryController::class, 'show'])->name('students.history');

    // Daily Tests & Marks Entry
    Route::resource('tests', TestController::class)->except(['destroy']);

    // Reports & Downloads
    Route::get('/reports/test/{test}', [ReportController::class, 'show'])->name('reports.test');
    Route::get('/reports/test/{test}/pdf', [ReportController::class, 'downloadPdf'])->name('reports.test.pdf');
    Route::get('/reports/test/{test}/csv', [ReportController::class, 'downloadCsv'])->name('reports.test.csv');
});
