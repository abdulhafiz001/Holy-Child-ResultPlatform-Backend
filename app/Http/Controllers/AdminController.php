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
            'password' => 'nullable|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password ?? 'password'), // Use provided password or default
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
     * Get admin profile
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();
        
        // Get teacher assignments if user is a teacher
        $assignments = [];
        if ($user->role === 'teacher') {
            $assignments = TeacherSubject::with(['schoolClass', 'subject'])
                                       ->where('teacher_id', $user->id)
                                       ->where('is_active', true)
                                       ->get()
                                       ->groupBy('class_id')
                                       ->map(function ($classAssignments) {
                                           $class = $classAssignments->first()->schoolClass;
                                           $class->subjects = $classAssignments->pluck('subject');
                                           return $class;
                                       })
                                       ->values();
        }

        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'middle_name' => $user->middle_name,
            'email' => $user->email,
            'username' => $user->username,
            'phone' => $user->phone,
            'address' => $user->address,
            'date_of_birth' => $user->date_of_birth,
            'gender' => $user->gender,
            'qualification' => $user->qualification,
            'department' => $user->department,
            'date_joined' => $user->created_at,
            'role' => $user->role,
            'is_form_teacher' => $user->is_form_teacher,
            'avatar' => $user->avatar,
            'assignments' => $assignments,
        ]);
    }

    /**
     * Update admin profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'qualification' => 'nullable|string|max:500',
            'department' => 'nullable|string|max:255',
        ]);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'qualification' => $request->qualification,
            'department' => $request->department,
        ]);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Change admin password
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|same:new_password',
        ]);

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password changed successfully'
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
            'password' => Hash::make('password'), // Hash the default password
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
            'subjects' => 'nullable|array',
            'subjects.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $student->update($request->all());

        // Update student subjects if provided
        if ($request->has('subjects')) {
            // Delete existing subject relationships completely
            $student->studentSubjects()->delete();
            
            // Create new subject relationships
            if ($request->subjects && count($request->subjects) > 0) {
                foreach ($request->subjects as $subjectName) {
                    $subject = Subject::where('name', $subjectName)->first();
                    if ($subject) {
                        \App\Models\StudentSubject::create([
                            'student_id' => $student->id,
                            'subject_id' => $subject->id,
                            'is_active' => true,
                        ]);
                    }
                }
            }
        }

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