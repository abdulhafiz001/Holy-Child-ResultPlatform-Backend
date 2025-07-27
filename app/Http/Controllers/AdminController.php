<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\Score;

class AdminController extends Controller
{
    /**
     * Get admin dashboard data
     */
    public function dashboard()
    {
        $totalStudents = Student::where('is_active', true)->count();
        $totalClasses = SchoolClass::where('is_active', true)->count();
        $totalSubjects = Subject::where('is_active', true)->count();
        $totalTeachers = User::where('role', 'teacher')->where('is_active', true)->count();
        
        $recentStudents = Student::with('schoolClass')
                                ->where('is_active', true)
                                ->latest()
                                ->take(5)
                                ->get();

        return response()->json([
            'stats' => [
                'total_students' => $totalStudents,
                'total_classes' => $totalClasses,
                'total_subjects' => $totalSubjects,
                'total_teachers' => $totalTeachers,
            ],
            'recent_students' => $recentStudents,
        ]);
    }

    /**
     * Get all users (admin and teachers)
     */
    public function getUsers()
    {
        $users = User::where('is_active', true)->get();
        return response()->json($users);
    }

    /**
     * Create a new user (admin or teacher)
     */
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|unique:users,username',
            'role' => 'required|in:admin,teacher',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make('password'), // Default password
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * Get a specific user
     */
    public function getUser(User $user)
    {
        return response()->json($user);
    }

    /**
     * Update a user
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'username' => 'required|string|unique:users,username,' . $user->id,
            'role' => 'required|in:admin,teacher',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $user->update($request->all());

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * Delete a user
     */
    public function deleteUser(Request $request, User $user)
    {
        // Check if this is a hard delete request
        $hardDelete = $request->query('hard_delete', false);

        if ($hardDelete === 'true') {
            // Hard delete - completely remove from database
            // The boot method in User model will handle cascade deletion
            $user->delete();
            return response()->json(['message' => 'User permanently deleted successfully']);
        } else {
            // Soft delete - just deactivate
            $user->update(['is_active' => false]);
            return response()->json(['message' => 'User deactivated successfully']);
        }
    }

    /**
     * Get teacher assignments
     */
    public function getTeacherAssignments()
    {
        $assignments = TeacherSubject::with(['teacher', 'subject', 'schoolClass'])
                                   ->where('is_active', true)
                                   ->get();

        return response()->json($assignments);
    }

    /**
     * Assign teacher to subject and class
     */
    public function assignTeacher(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
        ]);

        // Check if teacher is actually a teacher
        $teacher = User::find($request->teacher_id);
        if ($teacher->role !== 'teacher') {
            return response()->json(['message' => 'Selected user is not a teacher'], 400);
        }

        // Check if assignment already exists
        $existingAssignment = TeacherSubject::where([
            'teacher_id' => $request->teacher_id,
            'subject_id' => $request->subject_id,
            'class_id' => $request->class_id,
        ])->first();

        if ($existingAssignment) {
            return response()->json(['message' => 'Teacher is already assigned to this subject and class'], 400);
        }

        $assignment = TeacherSubject::create([
            'teacher_id' => $request->teacher_id,
            'subject_id' => $request->subject_id,
            'class_id' => $request->class_id,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Teacher assigned successfully',
            'assignment' => $assignment->load(['teacher', 'subject', 'schoolClass']),
        ], 201);
    }

    /**
     * Remove teacher assignment
     */
    public function removeTeacherAssignment(TeacherSubject $assignment)
    {
        $assignment->update(['is_active' => false]);
        
        return response()->json(['message' => 'Teacher assignment removed successfully']);
    }

    /**
     * Get all students
     */
    public function getStudents()
    {
        $students = Student::with(['schoolClass', 'studentSubjects.subject'])
                          ->where('is_active', true)
                          ->get();

        return response()->json($students);
    }

    /**
     * Create a new student
     */
    public function createStudent(Request $request)
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
            'subjects' => 'array',
            'subjects.*' => 'exists:subjects,id',
        ]);

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

        // Create student subject relationships
        if ($request->subjects) {
            foreach ($request->subjects as $subjectId) {
                \App\Models\StudentSubject::create([
                    'student_id' => $student->id,
                    'subject_id' => $subjectId,
                    'is_active' => true,
                ]);
            }
        }

        return response()->json([
            'message' => 'Student created successfully',
            'student' => $student->load(['schoolClass', 'studentSubjects.subject']),
        ], 201);
    }

    /**
     * Get a specific student
     */
    public function getStudent(Student $student)
    {
        return response()->json($student->load(['schoolClass', 'studentSubjects.subject']));
    }

    /**
     * Update a student
     */
    public function updateStudent(Request $request, Student $student)
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
    public function deleteStudent(Student $student)
    {
        $student->update(['is_active' => false]);
        
        return response()->json(['message' => 'Student deactivated successfully']);
    }
} 