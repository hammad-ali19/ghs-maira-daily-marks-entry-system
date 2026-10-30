<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'class_section_subject_id'])]
class TeacherAssignment extends Model
{
    /**
     * Get the teacher (user).
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the class-section-subject assignment.
     */
    public function classSectionSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSectionSubject::class);
    }
}
