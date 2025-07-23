<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\Score;

class TeacherController extends Controller
{
    /**
     * Get teacher dashboard data
     */
    public function dashboard(Request $request)
    {
        $teacher = $request->user();
        
        $assignments = TeacherSubject::with(['subject', 'schoolClass'])
                                   ->where('teacher_id', $teacher->id)
                                   ->where('is_active', true)
                                   ->get();

        $totalClasses = $assignments->count();
        $totalSubjects = $assignments->unique('subject_id')->count();
        
        $totalStudents = 0;
        foreach ($assignments as $assignment) {
            $totalStudents += $assignment->schoolClass->students()->where('is_active', true)->count();
        }

        $recentScores = Score::with(['student', 'subject', 'schoolClass'])
                            ->where('teacher_id', $teacher->id)
                            ->where('is_active', true)
                            ->latest()
                            ->take(5)
                            ->get();

        return response()->json([
            'teacher' => $teacher,
            'stats' => [
                'total_classes' => $totalClasses,
                'total_subjects' => $totalSubjects,
                'total_students' => $totalStudents,
            ],
            'assignments' => $assignments,
            'recent_scores' => $recentScores,
        ]);
    }

    /**
     * Get teacher's assignments
     */
    public function getAssignments(Request $request)
    {
        $teacher = $request->user();
        
        $assignments = TeacherSubject::with(['subject', 'schoolClass'])
                                   ->where('teacher_id', $teacher->id)
                                   ->where('is_active', true)
                                   ->get();

        return response()->json($assignments);
    }

    /**
     * Get students for teacher's assigned classes
     */
    public function getStudents(Request $request)
    {
        $teacher = $request->user();
        
        $classIds = TeacherSubject::where('teacher_id', $teacher->id)
                                 ->where('is_active', true)
                                 ->pluck('class_id');

        $query = Student::with(['schoolClass', 'studentSubjects.subject'])
                       ->whereIn('class_id', $classIds)
                       ->where('is_active', true);

        // Filter by class
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Search by name or admission number
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_number', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(20);

        return response()->json($students);
    }

    /**
     * Add student (form teacher only)
     */
    public function addStudent(Request $request)
    {
        $teacher = $request->user();
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'admission_number' => 'required|string|unique:students,admission_number',
            'email' => 'nullable|email|unique:students,email',
            'phone' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string',
            'parent_name' => 'nullable|string',
            'parent_phone' => 'nullable|string',
            'parent_email' => 'nullable|email',
            'class_id' => 'required|exists:classes,id',
            'subjects' => 'required|array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        // Check if teacher is assigned to this class
        $isAssigned = TeacherSubject::where('teacher_id', $teacher->id)
                                   ->where('class_id', $request->class_id)
                                   ->where('is_active', true)
                                   ->exists();

        if (!$isAssigned) {
            return response()->json(['message' => 'You are not assigned to this class'], 403);
        }

        $student = Student::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'admission_number' => $request->admission_number,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'address' => $request->address,
            'parent_name' => $request->parent_name,
            'parent_phone' => $request->parent_phone,
            'parent_email' => $request->parent_email,
            'class_id' => $request->class_id,
            'is_active' => true,
        ]);

        // Assign subjects to student
        foreach ($request->subjects as $subjectId) {
            \App\Models\StudentSubject::create([
                'student_id' => $student->id,
                'subject_id' => $subjectId,
                'is_active' => true,
            ]);
        }

        return response()->json([
            'message' => 'Student added successfully',
            'student' => $student->load(['schoolClass', 'studentSubjects.subject']),
        ], 201);
    }
} 