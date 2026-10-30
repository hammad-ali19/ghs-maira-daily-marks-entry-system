<?php

namespace Database\Factories;

use App\Models\ClassSection;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentEnrollment>
 */
class StudentEnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'class_section_id' => ClassSection::factory(),
            'enrolled_at' => now()->toDateString(),
            'left_at' => null,
            'is_current' => true,
        ];
    }
}
