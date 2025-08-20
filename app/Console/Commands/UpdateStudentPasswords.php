<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class UpdateStudentPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:update-passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing students with properly hashed default password "mypassword"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating student passwords with proper bcrypt hashing...');
        
        $students = Student::all();
        
        if ($students->isEmpty()) {
            $this->info('No students found in the database.');
            return 0;
        }
        
        $updatedCount = 0;
        
        foreach ($students as $student) {
            // Force update all passwords with fresh hashes
            $student->password = Hash::make('mypassword');
            $student->save();
            $updatedCount++;
            $this->info("Updated password for student: {$student->first_name} {$student->last_name} ({$student->admission_number})");
        }
        
        $this->info("Updated {$updatedCount} students with properly hashed default password.");
        $this->info('Default password is: mypassword');
        $this->info('All passwords are now properly hashed with bcrypt!');
        
        return 0;
    }
    
    /**
     * Check if a string is a valid bcrypt hash
     */
    private function isBcryptHash($password)
    {
        // Bcrypt hashes start with $2y$ and are 60 characters long
        return is_string($password) && 
               strlen($password) === 60 && 
               strpos($password, '$2y$') === 0;
    }
}
