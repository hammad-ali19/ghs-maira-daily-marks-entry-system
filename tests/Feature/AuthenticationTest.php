<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200)
        ->assertSee('Sign in to your account');
});

test('admin can log in successfully', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@ghsmaira.edu.pk',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
        'email' => 'admin@ghsmaira.edu.pk',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($admin);
});

test('teacher can log in successfully', function () {
    $teacher = User::factory()->teacher()->create([
        'email' => 'teacher@ghsmaira.edu.pk',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
        'email' => 'teacher@ghsmaira.edu.pk',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($teacher);
});

test('deactivated user cannot log in', function () {
    User::factory()->inactive()->create([
        'email' => 'inactive@ghsmaira.edu.pk',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
        'email' => 'inactive@ghsmaira.edu.pk',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('user can log out', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/login');
    $this->assertGuest();
});

test('unauthenticated users are redirected to login', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});
