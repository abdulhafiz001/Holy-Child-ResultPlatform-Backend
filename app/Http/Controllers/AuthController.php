<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Student;

class AuthController extends Controller
{
    /**
     * Login for admin and teachers
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
                   ->where('is_active', true)
                   ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'role' => $user->role,
        ]);
    }

    /**
     * Login for students
     */
    public function studentLogin(Request $request)
    {
        $request->validate([
            'admission_number' => 'required|string',
            'password' => 'required|string',
        ]);

        $student = Student::where('admission_number', $request->admission_number)
                         ->where('is_active', true)
                         ->first();

        if (!$student || $request->password !== 'mypassword') {
            throw ValidationException::withMessages([
                'admission_number' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $student->createToken('student-token')->plainTextToken;

        return response()->json([
            'student' => $student->load('schoolClass'),
            'token' => $token,
            'role' => 'student',
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        $user = $request->user();
        
        if ($user instanceof Student) {
            return response()->json([
                'user' => $user->load('schoolClass'),
                'role' => 'student',
            ]);
        }

        return response()->json([
            'user' => $user,
            'role' => $user->role,
        ]);
    }
} 