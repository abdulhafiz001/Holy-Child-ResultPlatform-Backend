<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\ClassSubject;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder creates only the essential data needed for production:
     * - Admin user
     * - School classes
     * - Subjects
     * - Class-subject assignments
     */
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin User',
                'email' => 'admin@holychild.edu.ng',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+234 801 234 5678',
                'address' => '123 Admin Street, Lagos',
                'is_active' => true,
            ]
        );

        if ($this->command) {
            $this->command->info('Admin user created successfully!');
            $this->command->info('Username: admin');
            $this->command->info('Password: password');
            $this->command->warn('Please change the default password after first login!');
        }

        // Create school classes
        $classes = [
            'JSS 1A', 'JSS 1B', 'JSS 2A', 'JSS 2B', 'JSS 3A', 'JSS 3B',
            'SS 1A', 'SS 1B', 'SS 2A', 'SS 2B', 'SS 3A', 'SS 3B'
        ];

        foreach ($classes as $className) {
            SchoolClass::firstOrCreate(
                ['name' => $className],
                [
                    'description' => $className . ' class',
                    'is_active' => true,
                ]
            );
        }

        if ($this->command) {
            $this->command->info('School classes created successfully!');
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
            ['name' => 'Christian Religious Studies', 'code' => 'CRS'],
            ['name' => 'Islamic Religious Studies', 'code' => 'IRS'],
            ['name' => 'French', 'code' => 'FRENCH'],
            ['name' => 'Yoruba', 'code' => 'YORUBA'],
            ['name' => 'Igbo', 'code' => 'IGBO'],
            ['name' => 'Hausa', 'code' => 'HAUSA'],
            ['name' => 'Business Studies', 'code' => 'BUS'],
            ['name' => 'Accounting', 'code' => 'ACC'],
            ['name' => 'Commerce', 'code' => 'COMM'],
            ['name' => 'Fine Arts', 'code' => 'ART'],
            ['name' => 'Music', 'code' => 'MUSIC'],
            ['name' => 'Physical Education', 'code' => 'PE'],
        ];

        foreach ($subjects as $subjectData) {
            Subject::firstOrCreate(
                ['name' => $subjectData['name']],
                [
                    'code' => $subjectData['code'],
                    'description' => $subjectData['name'] . ' subject',
                    'is_active' => true,
                ]
            );
        }

        if ($this->command) {
            $this->command->info('Subjects created successfully!');
        }

        // Assign subjects to classes
        $classSubjectAssignments = [
            // JSS 1 subjects
            ['JSS 1A', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Geography', 'History', 'Christian Religious Studies', 'French', 'Business Studies', 'Fine Arts', 'Physical Education']],
            ['JSS 1B', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Geography', 'History', 'Christian Religious Studies', 'French', 'Business Studies', 'Fine Arts', 'Physical Education']],
            // JSS 2 subjects
            ['JSS 2A', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Geography', 'History', 'Christian Religious Studies', 'French', 'Business Studies', 'Fine Arts', 'Physical Education']],
            ['JSS 2B', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Geography', 'History', 'Christian Religious Studies', 'French', 'Business Studies', 'Fine Arts', 'Physical Education']],
            // JSS 3 subjects
            ['JSS 3A', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Geography', 'History', 'Christian Religious Studies', 'French', 'Business Studies', 'Fine Arts', 'Physical Education']],
            ['JSS 3B', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Geography', 'History', 'Christian Religious Studies', 'French', 'Business Studies', 'Fine Arts', 'Physical Education']],
            // SS 1 subjects
            ['SS 1A', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Economics', 'Government', 'Literature', 'Christian Religious Studies', 'French', 'Business Studies', 'Fine Arts', 'Physical Education']],
            ['SS 1B', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Economics', 'Government', 'Literature', 'Christian Religious Studies', 'French', 'Business Studies', 'Fine Arts', 'Physical Education']],
            // SS 2 subjects
            ['SS 2A', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Economics', 'Government', 'Literature', 'Christian Religious Studies', 'French', 'Business Studies', 'Fine Arts', 'Physical Education']],
            ['SS 2B', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Economics', 'Government', 'Literature', 'Christian Religious Studies', 'French', 'Business Studies', 'Fine Arts', 'Physical Education']],
            // SS 3 subjects
            ['SS 3A', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Economics', 'Government', 'Literature', 'Christian Religious Studies', 'French', 'Business Studies', 'Fine Arts', 'Physical Education']],
            ['SS 3B', ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology', 'Economics', 'Government', 'Literature', 'Christian Religious Studies', 'French', 'Business Studies', 'Fine Arts', 'Physical Education']],
        ];

        foreach ($classSubjectAssignments as $assignment) {
            $className = $assignment[0];
            $subjectNames = $assignment[1];
            
            $class = SchoolClass::where('name', $className)->first();
            
            foreach ($subjectNames as $subjectName) {
                $subject = Subject::where('name', $subjectName)->first();
                
                if ($class && $subject) {
                    ClassSubject::firstOrCreate(
                        [
                            'class_id' => $class->id,
                            'subject_id' => $subject->id,
                        ],
                        [
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        if ($this->command) {
            $this->command->info('Class-subject assignments created successfully!');
            $this->command->info('Production data seeded successfully!');
            $this->command->info('The school admin can now add teachers and students through the admin panel.');
        }
    }
}
