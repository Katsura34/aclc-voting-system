<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Updates students table to have separate name fields and student details.
     */
    public function up(): void
    {
        // Add new columns to students table
        Schema::table('students', function (Blueprint $table) {
            $table->string('lastname')->after('usn');
            $table->string('firstname')->after('lastname');
            $table->string('strand')->nullable()->after('firstname');
            $table->string('year')->nullable()->after('strand');
            $table->enum('gender', ['Male', 'Female', 'Other'])->after('year');
        });

        // Migrate existing data (split name into firstname/lastname if data exists)
        $students = DB::table('students')->get();
        foreach ($students as $student) {
            $nameParts = explode(' ', trim($student->name), 2);
            $firstname = $nameParts[0] ?? '';
            $lastname = $nameParts[1] ?? $nameParts[0];
            
            DB::table('students')
                ->where('id', $student->id)
                ->update([
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'gender' => 'Other', // Default value, should be updated
                ]);
        }

        // Remove old columns
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['name', 'email']);
        });

        // Add indexes for better performance
        Schema::table('students', function (Blueprint $table) {
            $table->index(['lastname', 'firstname']);
            $table->index('year');
            $table->index('strand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back old columns
        Schema::table('students', function (Blueprint $table) {
            $table->string('name')->after('usn');
            $table->string('email')->unique()->after('name');
        });

        // Migrate data back
        $students = DB::table('students')->get();
        foreach ($students as $student) {
            $name = trim($student->firstname . ' ' . $student->lastname);
            $email = strtolower($student->usn) . '@student.aclc.edu.ph';
            
            DB::table('students')
                ->where('id', $student->id)
                ->update([
                    'name' => $name,
                    'email' => $email,
                ]);
        }

        // Remove new columns and indexes
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['lastname', 'firstname']);
            $table->dropIndex(['year']);
            $table->dropIndex(['strand']);
            $table->dropColumn(['lastname', 'firstname', 'strand', 'year', 'gender']);
        });
    }
};
