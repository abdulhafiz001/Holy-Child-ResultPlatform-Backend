<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Student extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'admission_number',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'parent_name',
        'parent_phone',
        'parent_email',
        'class_id',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get the student's full name
     */
    public function getFullNameAttribute()
    {
        $name = $this->first_name . ' ' . $this->last_name;
        if ($this->middle_name) {
            $name = $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name;
        }
        return $name;
    }

    /**
     * Get the student's class
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the student's subjects
     */
    public function studentSubjects()
    {
        return $this->hasMany(StudentSubject::class);
    }

    /**
     * Get the student's scores
     */
    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    /**
     * Get scores for a specific subject
     */
    public function getScoresForSubject($subjectId)
    {
        return $this->scores()->where('subject_id', $subjectId)->first();
    }
} 