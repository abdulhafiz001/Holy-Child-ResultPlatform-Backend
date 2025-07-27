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
        
        // Dashboard data
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    });
    
    // Teacher routes
    Route::middleware('teacher')->group(function () {
        Route::get('/teacher/assignments', [TeacherController::class, 'getAssignments']);
        Route::get('/teacher/classes', [TeacherController::class, 'getClasses']);
        Route::get('/teacher/subjects', [TeacherController::class, 'getSubjects']);
        Route::get('/teacher/students', [TeacherController::class, 'getStudents']);
        Route::get('/teacher/dashboard', [TeacherController::class, 'dashboard']);
        
        // Score management
        Route::get('/teacher/scores', [ScoreController::class, 'index']);
        Route::post('/teacher/scores', [ScoreController::class, 'store']);
        Route::put('/teacher/scores/{score}', [ScoreController::class, 'update']);
        Route::delete('/teacher/scores/{score}', [ScoreController::class, 'destroy']);
    });
    
    // Student routes
    Route::middleware('student')->group(function () {
        Route::get('/student/profile', [StudentController::class, 'getProfile']);
        Route::get('/student/results', [StudentController::class, 'getResults']);
        Route::get('/student/subjects', [StudentController::class, 'index']);
        Route::get('/student/dashboard', [StudentController::class, 'dashboard']);
    });
}); 