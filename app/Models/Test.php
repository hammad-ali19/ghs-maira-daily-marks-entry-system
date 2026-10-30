<?php

namespace App\Models;

use Database\Factories\TestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['class_section_subject_id', 'title', 'test_date', 'max_marks', 'created_by'])]
class Test extends Model
{
    /** @use HasFactory<TestFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'test_date' => 'date',
            'max_marks' => 'integer',
        ];
    }

    /**
     * Get the class-section-subject.
     */
    public function classSectionSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSectionSubject::class);
    }

    /**
     * Get the user who created the test.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get test results.
     */
    public function results(): HasMany
    {
        return $this->hasMany(TestResult::class);
    }
}
