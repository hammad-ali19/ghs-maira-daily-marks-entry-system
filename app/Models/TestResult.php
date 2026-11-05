<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['test_id', 'student_id', 'obtained_marks', 'is_absent'])]
class TestResult extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'obtained_marks' => 'decimal:1',
            'is_absent' => 'boolean',
        ];
    }

    /**
     * Get the test.
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * Get the student.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get percentage.
     */
    public function getPercentageAttribute(): ?float
    {
        if ($this->is_absent || $this->obtained_marks === null) {
            return null;
        }

        if ($this->test->max_marks <= 0) {
            return 0;
        }

        return round(($this->obtained_marks / $this->test->max_marks) * 100, 1);
    }
}
