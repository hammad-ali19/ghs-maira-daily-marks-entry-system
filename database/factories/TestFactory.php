<?php

namespace Database\Factories;

use App\Models\ClassSectionSubject;
use App\Models\Test;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Test>
 */
class TestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'class_section_subject_id' => ClassSectionSubject::factory(),
            'title' => fake()->sentence(3),
            'test_date' => fake()->date(),
            'max_marks' => fake()->randomElement([10, 20, 25, 50, 100]),
            'created_by' => User::factory()->teacher(),
        ];
    }
}
