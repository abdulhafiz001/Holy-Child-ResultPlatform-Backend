<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\SchoolClass;

class SubjectController extends Controller
{
    /**
     * Get all subjects
     */
    public function index()
    {
        $subjects = Subject::withCount(['classSubjects' => function ($query) {
            $query->where('is_active', true);
        }])->where('is_active', true)->get();

        return response()->json($subjects);
    }

    /**
     * Create a new subject
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:subjects,name',
            'code' => 'required|string|unique:subjects,code',
            'description' => 'nullable|string',
        ]);

        $subject = Subject::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Subject created successfully',
            'subject' => $subject,
        ], 201);
    }

    /**
     * Get a specific subject
     */
    public function show(Subject $subject)
    {
        return response()->json($subject->load([
            'classSubjects.schoolClass',
            'teacherSubjects.teacher',
            'teacherSubjects.schoolClass'
        ]));
    }

    /**
     * Update a subject
     */
    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string|unique:subjects,name,' . $subject->id,
            'code' => 'required|string|unique:subjects,code,' . $subject->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $subject->update($request->all());

        return response()->json([
            'message' => 'Subject updated successfully',
            'subject' => $subject,
        ]);
    }

    /**
     * Delete a subject
     */
    public function destroy(Subject $subject)
    {
        // Check if subject is assigned to any classes
        $classCount = $subject->classSubjects()->where('is_active', true)->count();
        
        if ($classCount > 0) {
            return response()->json([
                'message' => 'Cannot delete subject that is assigned to classes. Please remove assignments first.'
            ], 400);
        }

        $subject->update(['is_active' => false]);
        
        return response()->json(['message' => 'Subject deactivated successfully']);
    }
} 