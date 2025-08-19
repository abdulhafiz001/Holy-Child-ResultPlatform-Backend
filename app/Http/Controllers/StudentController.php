<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\StudentSubject;
use App\Models\Score;
use App\Models\TeacherSubject;

class StudentController extends Controller
{
    /**
     * Get all students (admin)
     */
    public function index(Request $request)
    {
        $query = Student::with(['schoolClass', 'studentSubjects.subject'])
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
     * Create a new student (admin or form teacher)
     */
    public function store(Request $request)
    {
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

        // Check if the authenticated user is a form teacher for this class
        if (auth()->user()->isTeacher()) {
            $isFormTeacher = TeacherSubject::where('teacher_id', auth()->id())
                                          ->where('class_id', $request->class_id)
                                          ->exists();
            
            if (!$isFormTeacher) {
                return response()->json(['message' => 'Only the form teacher can add students to this class'], 403);
            }
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
            StudentSubject::create([
                'student_id' => $student->id,
                'subject_id' => $subjectId,
                'is_active' => true,
            ]);
        }

        return response()->json([
            'message' => 'Student created successfully',
            'student' => $student->load(['schoolClass', 'studentSubjects.subject']),
        ], 201);
    }

    /**
     * Get a specific student
     */
    public function show(Student $student)
    {
        return response()->json($student->load(['schoolClass', 'studentSubjects.subject']));
    }

    /**
     * Update a student
     */
    public function update(Request $request, Student $student)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'admission_number' => 'required|string|unique:students,admission_number,' . $student->id,
            'email' => 'nullable|email|unique:students,email,' . $student->id,
            'phone' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string',
            'parent_name' => 'nullable|string',
            'parent_phone' => 'nullable|string',
            'parent_email' => 'nullable|email',
            'class_id' => 'required|exists:classes,id',
            'is_active' => 'boolean',
        ]);

        $student->update($request->all());

        return response()->json([
            'message' => 'Student updated successfully',
            'student' => $student->load(['schoolClass', 'studentSubjects.subject']),
        ]);
    }

    /**
     * Delete a student
     */
    public function destroy(Student $student)
    {
        $student->update(['is_active' => false]);
        
        return response()->json(['message' => 'Student deactivated successfully']);
    }

    /**
     * Student dashboard (for student access)
     */
    public function dashboard(Request $request)
    {
        $student = $request->user();
        
        $scores = Score::with(['subject'])
                      ->where('student_id', $student->id)
                      ->where('is_active', true)
                      ->get();

        $totalSubjects = $student->studentSubjects()->count();
        $completedSubjects = $scores->count();

        return response()->json([
            'student' => $student->load('schoolClass'),
            'stats' => [
                'total_subjects' => $totalSubjects,
                'completed_subjects' => $completedSubjects,
            ],
            'recent_scores' => $scores->take(5),
        ]);
    }

    /**
     * Get student results (for student access)
     */
    public function getResults(Request $request)
    {
        $student = $request->user();
        
        $scores = Score::with(['subject'])
                      ->where('student_id', $student->id)
                      ->where('is_active', true)
                      ->get();

        return response()->json($scores);
    }

    /**
     * Get student profile (for student access)
     */
    public function getProfile(Request $request)
    {
        $student = $request->user();
        
        return response()->json($student->load(['schoolClass', 'studentSubjects.subject']));
    }

    /**
     * Update student profile (for student access)
     */
    public function updateProfile(Request $request)
    {
        $student = $request->user();
        
        $request->validate([
            'email' => 'nullable|email|unique:students,email,' . $student->id,
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'parent_phone' => 'nullable|string',
            'parent_email' => 'nullable|email',
        ]);

        $student->update($request->only([
            'email', 'phone', 'address', 'parent_phone', 'parent_email'
        ]));

        return response()->json([
            'message' => 'Profile updated successfully',
            'student' => $student->load(['schoolClass', 'studentSubjects.subject']),
        ]);
    }

    /**
     * Change student password
     */
    public function changePassword(Request $request)
    {
        $student = $request->user();
        
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|same:new_password',
        ]);

        // Check current password
        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $student->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // Update password
        $student->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }
} 