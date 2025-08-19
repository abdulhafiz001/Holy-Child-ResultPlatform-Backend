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
        'exam_score',
        'total_score',
        'grade',
        'remark',
        'term',
        'is_active',
    ];

    protected $casts = [
        'first_ca' => 'decimal:2',
        'second_ca' => 'decimal:2',
        'exam_score' => 'decimal:2',
        'total_score' => 'decimal:2',
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
        $this->total_score = ($this->first_ca ?? 0) + ($this->second_ca ?? 0) + ($this->exam_score ?? 0);
        return $this->total_score;
    }

    /**
     * Calculate grade based on total score
     */
    public function calculateGrade()
    {
        $total = $this->calculateTotal();
        
        if ($total >= 80) {
            $this->grade = 'A';
        } elseif ($total >= 70) {
            $this->grade = 'B';
        } elseif ($total >= 60) {
            $this->grade = 'C';
        } elseif ($total >= 50) {
            $this->grade = 'D';
        } elseif ($total >= 40) {
            $this->grade = 'E';
        } else {
            $this->grade = 'F';
        }
        
        return $this->grade;
    }

    /**
     * Boot method to automatically calculate total and grade
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($score) {
            $score->calculateTotal();
            $score->calculateGrade();
        });

        static::updating(function ($score) {
            $score->calculateTotal();
            $score->calculateGrade();
        });
    }
} 