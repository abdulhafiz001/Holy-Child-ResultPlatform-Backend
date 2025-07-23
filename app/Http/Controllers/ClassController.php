<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;

class ClassController extends Controller
{
    /**
     * Get all classes
     */
    public function index()
    {
        $classes = SchoolClass::withCount(['students' => function ($query) {
            $query->where('is_active', true);
        }])->where('is_active', true)->get();

        return response()->json($classes);
    }

    /**
     * Create a new class
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:classes,name',
            'description' => 'nullable|string',
        ]);

        $class = SchoolClass::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Class created successfully',
            'class' => $class,
        ], 201);
    }

    /**
     * Get a specific class
     */
    public function show(SchoolClass $class)
    {
        return response()->json($class->load([
            'students' => function ($query) {
                $query->where('is_active', true);
            },
            'students.studentSubjects.subject'
        ]));
    }

    /**
     * Update a class
     */
    public function update(Request $request, SchoolClass $class)
    {
        $request->validate([
            'name' => 'required|string|unique:classes,name,' . $class->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $class->update($request->all());

        return response()->json([
            'message' => 'Class updated successfully',
            'class' => $class,
        ]);
    }

    /**
     * Delete a class
     */
    public function destroy(SchoolClass $class)
    {
        // Check if class has students
        $studentCount = $class->students()->where('is_active', true)->count();
        
        if ($studentCount > 0) {
            return response()->json([
                'message' => 'Cannot delete class with active students. Please transfer students first.'
            ], 400);
        }

        $class->update(['is_active' => false]);
        
        return response()->json(['message' => 'Class deactivated successfully']);
    }
} 