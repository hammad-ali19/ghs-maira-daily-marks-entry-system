<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Urdu', 'English', 'Mathematics', 'General Science', 'Islamiat',
                'Pakistan Studies', 'Physics', 'Chemistry', 'Biology', 'Computer Science',
            ]),
            'is_active' => true,
        ];
    }
}
