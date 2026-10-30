<?php

namespace App\Models;

use Database\Factories\ClassSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['school_class_id', 'name', 'incharge_id', 'is_active'])]
class ClassSection extends Model
{
    /** @use HasFactory<ClassSectionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the parent school class.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    /**
     * Get the incharge teacher.
     */
    public function incharge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'incharge_id');
    }

    /**
     * Get student enrollments for this section.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    /**
     * Get current student enrollments.
     */
    public function currentEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class)->where('is_current', true);
    }

    /**
     * Get class-section-subject assignments.
     */
    public function classSectionSubjects(): HasMany
    {
        return $this->hasMany(ClassSectionSubject::class);
    }

    /**
     * Get the full display name (e.g. "6th A").
     */
    public function getFullNameAttribute(): string
    {
        if ($this->name === 'Default') {
            return $this->schoolClass->name;
        }

        return $this->schoolClass->name.' '.$this->name;
    }
}
