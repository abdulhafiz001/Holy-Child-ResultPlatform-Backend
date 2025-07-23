<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\TeacherSubject;
use App\Models\StudentSubject;
use App\Models\Score;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@tgcra.com',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+234 801 234 5678',
            'address' => '123 Admin Street, Lagos',
            'is_active' => true,
        ]);

        // Create sample teachers
        $teachers = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@tgcra.com',
                'username' => 'jsmith',
                'phone' => '+234 802 345 6789',
                'address' => '456 Teacher Avenue, Lagos',
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@tgcra.com',
                'username' => 'sjohnson',
                'phone' => '+234 803 456 7890',
                'address' => '789 Educator Road, Lagos',
            ],
            [
                'name' => 'Michael Brown',
                'email' => 'michael.brown@tgcra.com',
                'username' => 'mbrown',
                'phone' => '+234 804 567 8901',
                'address' => '321 Instructor Lane, Lagos',
            ],
        ];

        foreach ($teachers as $teacherData) {
            User::create([
                ...$teacherData,
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'is_active' => true,
            ]);
        }

        // Create classes
        $classes = [
            'JSS 1A', 'JSS 1B', 'JSS 2A', 'JSS 2B', 'JSS 3A', 'JSS 3B',
            'SS 1A', 'SS 1B', 'SS 2A', 'SS 2B', 'SS 3A', 'SS 3B'
        ];

        foreach ($classes as $className) {
            SchoolClass::create([
                'name' => $className,
                'description' => $className . ' class',
                'is_active' => true,
            ]);
        }

        // Create subjects
        $subjects = [
            ['name' => 'Mathematics', 'code' => 'MATH'],
            ['name' => 'English Language', 'code' => 'ENG'],
            ['name' => 'Physics', 'code' => 'PHY'],
            ['name' => 'Chemistry', 'code' => 'CHEM'],
            ['name' => 'Biology', 'code' => 'BIO'],
            ['name' => 'Geography', 'code' => 'GEO'],
            ['name' => 'History', 'code' => 'HIST'],
            ['name' => 'Economics', 'code' => 'ECON'],
            ['name' => 'Government', 'code' => 'GOVT'],
            ['name' => 'Literature', 'code' => 'LIT'],
            ['name' => 'Further Mathematics', 'code' => 'FURMATH'],
            ['name' => 'Computer Science', 'code' => 'COMP'],
            ['name' => 'Agricultural Science', 'code' => 'AGRIC'],
        ];

        foreach ($subjects as $subjectData) {
            Subject::create([
                ...$subjectData,
                'description' => $subjectData['name'] . ' subject',
                'is_active' => true,
            ]);
        }

        // Assign subjects to classes
        $classSubjectAssignments = [
            // JSS 1 subjects
            ['JSS 1A', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Geography', 'History']],
            ['JSS 1B', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Geography', 'History']],
            // JSS 2 subjects
            ['JSS 2A', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Geography', 'History']],
            ['JSS 2B', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Geography', 'History']],
            // JSS 3 subjects
            ['JSS 3A', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Geography', 'History']],
            ['JSS 3B', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Geography', 'History']],
            // SS 1 subjects
            ['SS 1A', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Economics', 'Government']],
            ['SS 1B', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Economics', 'Government']],
            // SS 2 subjects
            ['SS 2A', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Economics', 'Government']],
            ['SS 2B', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Economics', 'Government']],
            // SS 3 subjects
            ['SS 3A', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Economics', 'Government']],
            ['SS 3B', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Economics', 'Government']],
        ];

        foreach ($classSubjectAssignments as $assignment) {
            $className = $assignment[0];
            $subjectNames = $assignment[1];
            
            $class = SchoolClass::where('name', $className)->first();
            
            foreach ($subjectNames as $subjectName) {
                $subject = Subject::where('name', $subjectName)->first();
                
                if ($class && $subject) {
                    ClassSubject::create([
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'is_active' => true,
                    ]);
                }
            }
        }

        // Assign teachers to subjects and classes
        $teacherAssignments = [
            ['jsmith', 'Mathematics', ['JSS 1A', 'JSS 2A', 'SS 1A']],
            ['sjohnson', 'English Language', ['JSS 1B', 'JSS 2B', 'SS 1B']],
            ['mbrown', 'Physics', ['JSS 3A', 'SS 2A', 'SS 3A']],
        ];

        foreach ($teacherAssignments as $assignment) {
            $username = $assignment[0];
            $subjectName = $assignment[1];
            $classNames = $assignment[2];
            
            $teacher = User::where('username', $username)->first();
            $subject = Subject::where('name', $subjectName)->first();
            
            if ($teacher && $subject) {
                foreach ($classNames as $className) {
                    $class = SchoolClass::where('name', $className)->first();
                    
                    if ($class) {
                        TeacherSubject::create([
                            'teacher_id' => $teacher->id,
                            'subject_id' => $subject->id,
                            'class_id' => $class->id,
                            'is_active' => true,
                        ]);
                    }
                }
            }
        }

        // Create sample students
        $students = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'admission_number' => 'ADM/2024/001',
                'email' => 'john.doe@student.com',
                'class' => 'JSS 1A',
                'subjects' => ['Mathematics', 'English Language', 'Physics', 'Chemistry'],
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'admission_number' => 'ADM/2024/002',
                'email' => 'jane.smith@student.com',
                'class' => 'JSS 1A',
                'subjects' => ['Mathematics', 'English Language', 'Biology', 'Geography'],
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'admission_number' => 'ADM/2024/003',
                'email' => 'michael.johnson@student.com',
                'class' => 'JSS 2A',
                'subjects' => ['Mathematics', 'English Language', 'Physics', 'Computer Science'],
            ],
        ];

        foreach ($students as $studentData) {
            $className = $studentData['class'];
            $subjectNames = $studentData['subjects'];
            
            $class = SchoolClass::where('name', $className)->first();
            
            if ($class) {
                $student = Student::create([
                    'first_name' => $studentData['first_name'],
                    'last_name' => $studentData['last_name'],
                    'admission_number' => $studentData['admission_number'],
                    'email' => $studentData['email'],
                    'class_id' => $class->id,
                    'is_active' => true,
                ]);

                // Assign subjects to student
                foreach ($subjectNames as $subjectName) {
                    $subject = Subject::where('name', $subjectName)->first();
                    
                    if ($subject) {
                        StudentSubject::create([
                            'student_id' => $student->id,
                            'subject_id' => $subject->id,
                            'is_active' => true,
                        ]);
                    }
                }
            }
        }
    }
}
