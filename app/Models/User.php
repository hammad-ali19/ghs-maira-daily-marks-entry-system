<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a teacher.
     */
    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    /**
     * Get class sections where this user is incharge.
     */
    public function inchargeOf(): HasMany
    {
        return $this->hasMany(ClassSection::class, 'incharge_id');
    }

    /**
     * Get teacher assignments.
     */
    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    /**
     * Get tests created by this user.
     */
    public function createdTests(): HasMany
    {
        return $this->hasMany(Test::class, 'created_by');
    }

    /**
     * Check if user is incharge of a specific class section.
     */
    public function isInchargeOf(ClassSection $classSection): bool
    {
        return $this->id === $classSection->incharge_id;
    }

    /**
     * Check if user is assigned to teach a specific class-section-subject.
     */
    public function isAssignedTo(ClassSectionSubject $classSectionSubject): bool
    {
        return $this->teacherAssignments()
            ->where('class_section_subject_id', $classSectionSubject->id)
            ->exists();
    }
}
