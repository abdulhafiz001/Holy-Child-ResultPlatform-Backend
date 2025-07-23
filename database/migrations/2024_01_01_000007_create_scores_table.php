<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->decimal('first_ca', 5, 2)->default(0);
            $table->decimal('second_ca', 5, 2)->default(0);
            $table->decimal('exam', 5, 2)->default(0);
            $table->decimal('total', 5, 2)->default(0);
            $table->string('grade', 2)->nullable();
            $table->string('remark')->nullable();
            $table->string('term')->default('First Term');
            $table->string('academic_year')->default('2024/2025');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'term', 'academic_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
}; 