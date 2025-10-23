<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ScoreController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/test', function () {
    return response()->json(['status' => 'success', 'message' => 'API is working!']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/student/login', [AuthController::class, 'studentLogin']);
Route::post('/auth/teacher/login', [AuthController::class, 'login']); // Teacher login uses same endpoint as admin

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Admin routes
    Route::middleware('admin')->group(function () {
        // User management
        Route::get('/admin/users', [AdminController::class, 'getUsers']);
        Route::post('/admin/users', [AdminController::class, 'createUser']);
        Route::get('/admin/users/{user}', [AdminController::class, 'getUser']);
        Route::put('/admin/users/{user}', [AdminController::class, 'updateUser']);
        Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUser']);
        
        // Class management
        Route::get('/admin/classes', [ClassController::class, 'index']);
        Route::post('/admin/classes', [ClassController::class, 'store']);
        Route::get('/admin/classes/{class}', [ClassController::class, 'show']);
        Route::put('/admin/classes/{class}', [ClassController::class, 'update']);
        Route::delete('/admin/classes/{class}', [ClassController::class, 'destroy']);
        
        // Subject management
        Route::get('/admin/subjects', [SubjectController::class, 'index']);
        Route::post('/admin/subjects', [SubjectController::class, 'store']);
        Route::get('/admin/subjects/{subject}', [SubjectController::class, 'show']);
        Route::put('/admin/subjects/{subject}', [SubjectController::class, 'update']);
        Route::delete('/admin/subjects/{subject}', [SubjectController::class, 'destroy']);
        
        // Teacher assignments
        Route::get('/admin/teacher-assignments', [AdminController::class, 'getTeacherAssignments']);
        Route::post('/admin/teacher-assignments', [AdminController::class, 'assignTeacher']);
        Route::delete('/admin/teacher-assignments/{assignment}', [AdminController::class, 'removeTeacherAssignment']);
        
        // Student management
        Route::get('/admin/students', [AdminController::class, 'getStudents']);
        Route::post('/admin/students', [AdminController::class, 'createStudent']);
        Route::get('/admin/students/{student}', [AdminController::class, 'getStudent']);
        Route::put('/admin/students/{student}', [AdminController::class, 'updateStudent']);
        Route::delete('/admin/students/{student}', [AdminController::class, 'deleteStudent']);
        
        // Score management
        Route::get('/admin/scores', [ScoreController::class, 'adminIndex']);
        Route::get('/admin/students/{student}/results', [ScoreController::class, 'adminStudentResults']);
        Route::get('/admin/classes/{class}/results', [ScoreController::class, 'getClassResults']);
        
        // Dashboard data
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
        
        // Profile management
        Route::get('/admin/profile', [AdminController::class, 'getProfile']);
        Route::put('/admin/profile', [AdminController::class, 'updateProfile']);
        Route::put('/admin/change-password', [AdminController::class, 'changePassword']);
    });
    
    // Teacher routes
    Route::middleware('teacher')->group(function () {
        Route::get('/teacher/assignments', [TeacherController::class, 'getAssignments']);
        Route::get('/teacher/classes', [TeacherController::class, 'getClasses']);
        Route::get('/teacher/form-teacher-classes', [TeacherController::class, 'getFormTeacherClasses']);
        Route::get('/teacher/subjects', [TeacherController::class, 'getSubjects']);
        Route::get('/teacher/subjects/all', [TeacherController::class, 'getAllSubjects']);
        Route::get('/teacher/students', [TeacherController::class, 'getStudents']);
        Route::post('/teacher/students', [TeacherController::class, 'addStudent']);
        Route::put('/teacher/students/{student}', [TeacherController::class, 'updateStudent']);
        Route::delete('/teacher/students/{student}', [TeacherController::class, 'deleteStudent']);
        Route::get('/teacher/dashboard', [TeacherController::class, 'dashboard']);
        
        // Profile management
        Route::get('/teacher/profile', [TeacherController::class, 'getProfile']);
        Route::put('/teacher/profile', [TeacherController::class, 'updateProfile']);
        Route::put('/teacher/change-password', [TeacherController::class, 'changePassword']);
        
        // Score management
        Route::get('/teacher/scores', [ScoreController::class, 'teacherIndex']);
        Route::get('/teacher/scores/assignments', [ScoreController::class, 'getTeacherAssignmentsForScores']);
        Route::get('/teacher/scores/students', [ScoreController::class, 'getStudentsForClassSubject']);
        Route::get('/teacher/scores/existing', [ScoreController::class, 'getExistingScores']);
        Route::get('/teacher/scores/subject', [ScoreController::class, 'getSubjectScores']);
        Route::post('/teacher/scores', [ScoreController::class, 'store']);
        Route::put('/teacher/scores/{score}', [ScoreController::class, 'update']);
        
        // Student scores
        Route::get('/teacher/students/{student}/scores', [ScoreController::class, 'getStudentScores']);
        
        // Student results (for form teachers)
        Route::get('/teacher/students/{student}/results', [ScoreController::class, 'teacherStudentResults']);
        
        // Student results page (for form teachers)
        Route::get('/teacher/student-results/{student}', [ScoreController::class, 'teacherStudentResults']);
        
        // Class results (for form teachers)
        Route::get('/teacher/classes/{class}/results', [ScoreController::class, 'getClassResults']);
        
        // Check form teacher status
        Route::get('/teacher/form-teacher-status', [TeacherController::class, 'checkFormTeacherStatus']);
    });
    
    // Form Teacher routes (can access some admin endpoints with restrictions)
    Route::middleware(['teacher', 'form.teacher'])->group(function () {
        Route::get('/form-teacher/scores', [ScoreController::class, 'adminIndex']);
        Route::get('/form-teacher/classes', [ClassController::class, 'index']);
        Route::get('/form-teacher/classes/{class}', [ClassController::class, 'show']);
        Route::get('/form-teacher/classes/{class}/results', [ScoreController::class, 'getClassResults']);
        Route::get('/form-teacher/debug', [ClassController::class, 'debugFormTeacher']);
    });
    
    // Student routes
    Route::middleware('student')->group(function () {
        Route::get('/student/profile', [StudentController::class, 'getProfile']);
        Route::put('/student/profile', [StudentController::class, 'updateProfile']);
        Route::put('/student/change-password', [StudentController::class, 'changePassword']);
        Route::get('/student/results', [StudentController::class, 'getResults']);
        Route::get('/student/subjects', [StudentController::class, 'getSubjects']);
        Route::get('/student/dashboard', [StudentController::class, 'dashboard']);
    });
}); 