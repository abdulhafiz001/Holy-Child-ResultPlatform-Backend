<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'class_id',
        'teacher_id',
        'first_ca',
        'second_ca',
        'exam',
        'total',
        'grade',
        'remark',
        'term',
        'academic_year',
        'is_active',
    ];

    protected $casts = [
        'first_ca' => 'decimal:2',
        'second_ca' => 'decimal:2',
        'exam' => 'decimal:2',
        'total' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the student
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the subject
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the class
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the teacher who recorded the score
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Calculate total score
     */
    public function calculateTotal()
    {
        $this->total = ($this->first_ca ?? 0) + ($this->second_ca ?? 0) + ($this->exam ?? 0);
        return $this->total;
    }

    /**
     * Calculate grade based on total score
     */
    public function calculateGrade()
    {
        $total = $this->calculateTotal();
        
        if ($total >= 90) {
            $this->grade = 'A1';
            $this->remark = 'Excellent';
        } elseif ($total >= 80) {
            $this->grade = 'B2';
            $this->remark = 'Very Good';
        } elseif ($total >= 70) {
            $this->grade = 'B3';
            $this->remark = 'Good';
        } elseif ($total >= 60) {
            $this->grade = 'C4';
            $this->remark = 'Credit';
        } elseif ($total >= 50) {
            $this->grade = 'C5';
            $this->remark = 'Credit';
        } elseif ($total >= 45) {
            $this->grade = 'C6';
            $this->remark = 'Credit';
        } elseif ($total >= 40) {
            $this->grade = 'D7';
            $this->remark = 'Pass';
        } elseif ($total >= 35) {
            $this->grade = 'E8';
            $this->remark = 'Pass';
        } else {
            $this->grade = 'F9';
            $this->remark = 'Fail';
        }
        
        return $this->grade;
    }

    /**
     * Boot method to automatically calculate total and grade
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($score) {
            $score->calculateTotal();
            $score->calculateGrade();
        });
    }
} 