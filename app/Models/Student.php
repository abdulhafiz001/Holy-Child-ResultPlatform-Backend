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
        'password',
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

    /**
     * Hash the password when setting it
     */
    public function setPasswordAttribute($value)
    {
        // Only hash if the value is not already a bcrypt hash
        if (!$this->isBcryptHash($value)) {
            $this->attributes['password'] = bcrypt($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }
    
    /**
     * Check if a string is a valid bcrypt hash
     */
    private function isBcryptHash($password)
    {
        // Bcrypt hashes start with $2y$ and are 60 characters long
        return is_string($password) && 
               strlen($password) === 60 && 
               strpos($password, '$2y$') === 0;
    }

    /**
     * Verify the student's password
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * Check if student is active
     */
    public function isActive()
    {
        return $this->is_active;
    }
} 