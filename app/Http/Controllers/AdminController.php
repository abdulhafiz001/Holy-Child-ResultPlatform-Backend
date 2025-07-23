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
    public function deleteUser(User $user)
    {
        $user->update(['is_active' => false]);
        
        return response()->json(['message' => 'User deactivated successfully']);
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
} 