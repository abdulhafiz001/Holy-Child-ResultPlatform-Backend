<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\TeacherSubject;

class ScoreController extends Controller
{
    /**
     * Get scores for admin view
     */
    public function adminIndex(Request $request)
    {
        $query = Score::with(['student', 'subject', 'schoolClass', 'teacher'])
                     ->where('is_active', true);

        // Filter by class
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by subject
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by term
        if ($request->has('term')) {
            $query->where('term', $request->term);
        }

        $scores = $query->paginate(20);

        return response()->json($scores);
    }

    /**
     * Get scores for teacher view
     */
    public function teacherIndex(Request $request)
    {
        $teacher = $request->user();
        
        $query = Score::with(['student', 'subject', 'schoolClass'])
                     ->where('teacher_id', $teacher->id)
                     ->where('is_active', true);

        // Filter by class
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by subject
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by term
        if ($request->has('term')) {
            $query->where('term', $request->term);
        }

        $scores = $query->paginate(20);

        return response()->json($scores);
    }

    /**
     * Get scores for a specific class (teacher view)
     */
    public function getClassScores(Request $request, SchoolClass $class)
    {
        $teacher = $request->user();
        
        // Check if teacher is assigned to this class
        $assignments = TeacherSubject::where('teacher_id', $teacher->id)
                                   ->where('class_id', $class->id)
                                   ->where('is_active', true)
                                   ->with('subject')
                                   ->get();

        if ($assignments->isEmpty()) {
            return response()->json(['message' => 'You are not assigned to this class'], 403);
        }

        $students = $class->students()
                         ->where('is_active', true)
                         ->with(['scores' => function ($query) use ($teacher) {
                             $query->where('teacher_id', $teacher->id)
                                   ->where('is_active', true)
                                   ->with('subject');
                         }])
                         ->get();

        return response()->json([
            'class' => $class,
            'assignments' => $assignments,
            'students' => $students,
        ]);
    }

    /**
     * Store a new score
     */
    public function store(Request $request)
    {
        $teacher = $request->user();
        
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'first_ca' => 'nullable|numeric|min:0|max:20',
            'second_ca' => 'nullable|numeric|min:0|max:20',
            'exam' => 'nullable|numeric|min:0|max:60',
            'term' => 'required|string',
            'academic_year' => 'required|string',
        ]);

        // Check if teacher is assigned to this subject and class
        $isAssigned = TeacherSubject::where('teacher_id', $teacher->id)
                                   ->where('subject_id', $request->subject_id)
                                   ->where('class_id', $request->class_id)
                                   ->where('is_active', true)
                                   ->exists();

        if (!$isAssigned) {
            return response()->json(['message' => 'You are not assigned to this subject and class'], 403);
        }

        // Check if score already exists for this student, subject, term, and academic year
        $existingScore = Score::where('student_id', $request->student_id)
                             ->where('subject_id', $request->subject_id)
                             ->where('term', $request->term)
                             ->where('academic_year', $request->academic_year)
                             ->first();

        if ($existingScore) {
            return response()->json(['message' => 'Score already exists for this student, subject, term, and academic year'], 400);
        }

        $score = Score::create([
            'student_id' => $request->student_id,
            'subject_id' => $request->subject_id,
            'class_id' => $request->class_id,
            'teacher_id' => $teacher->id,
            'first_ca' => $request->first_ca ?? 0,
            'second_ca' => $request->second_ca ?? 0,
            'exam' => $request->exam ?? 0,
            'term' => $request->term,
            'academic_year' => $request->academic_year,
            'is_active' => true,
        ]);

        // The total, grade, and remark will be calculated automatically by the model

        return response()->json([
            'message' => 'Score recorded successfully',
            'score' => $score->load(['student', 'subject', 'schoolClass']),
        ], 201);
    }

    /**
     * Update a score
     */
    public function update(Request $request, Score $score)
    {
        $teacher = $request->user();
        
        // Check if teacher owns this score
        if ($score->teacher_id !== $teacher->id) {
            return response()->json(['message' => 'You can only update your own scores'], 403);
        }

        $request->validate([
            'first_ca' => 'nullable|numeric|min:0|max:20',
            'second_ca' => 'nullable|numeric|min:0|max:20',
            'exam' => 'nullable|numeric|min:0|max:60',
        ]);

        $score->update([
            'first_ca' => $request->first_ca ?? $score->first_ca,
            'second_ca' => $request->second_ca ?? $score->second_ca,
            'exam' => $request->exam ?? $score->exam,
        ]);

        // The total, grade, and remark will be recalculated automatically

        return response()->json([
            'message' => 'Score updated successfully',
            'score' => $score->load(['student', 'subject', 'schoolClass']),
        ]);
    }

    /**
     * Delete a score
     */
    public function destroy(Score $score)
    {
        $teacher = $request->user();
        
        // Check if teacher owns this score
        if ($score->teacher_id !== $teacher->id) {
            return response()->json(['message' => 'You can only delete your own scores'], 403);
        }

        $score->update(['is_active' => false]);
        
        return response()->json(['message' => 'Score deleted successfully']);
    }

    /**
     * Get student results for admin view
     */
    public function adminStudentResults(Student $student)
    {
        $scores = Score::with(['subject', 'teacher', 'schoolClass'])
                      ->where('student_id', $student->id)
                      ->where('is_active', true)
                      ->get();

        return response()->json([
            'student' => $student->load(['schoolClass', 'studentSubjects.subject']),
            'scores' => $scores,
        ]);
    }
} 