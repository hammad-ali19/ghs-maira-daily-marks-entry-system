<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'b_form_number' => fake()->unique()->numerify('#####-#######-#'),
            'photo_path' => 'students/default.png',
            'is_active' => true,
        ];
    }
}
