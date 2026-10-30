<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure default initial administrator account exists
        User::firstOrCreate(
            ['email' => 'admin@ghsmaira.edu.pk'],
            [
                'name' => 'Headmaster / Administrator',
                'password' => Hash::make('Admin123!'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
