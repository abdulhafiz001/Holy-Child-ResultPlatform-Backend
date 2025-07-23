<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get students in this class
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    /**
     * Get subjects taught in this class
     */
    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class, 'class_id');
    }

    /**
     * Get teachers assigned to this class through subjects
     */
    public function teachers()
    {
        return $this->hasManyThrough(User::class, TeacherSubject::class, 'class_id', 'id', 'id', 'teacher_id');
    }

    /**
     * Get the form teacher (first teacher assigned to this class)
     */
    public function formTeacher()
    {
        return $this->teachers()->first();
    }
} 