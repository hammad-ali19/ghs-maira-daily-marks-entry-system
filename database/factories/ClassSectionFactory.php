<?php

namespace Database\Factories;

use App\Models\ClassSection;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassSection>
 */
class ClassSectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_class_id' => SchoolClass::factory(),
            'name' => fake()->randomElement(['A', 'B', 'C', 'Default']),
            'incharge_id' => User::factory()->teacher(),
            'is_active' => true,
        ];
    }
}
