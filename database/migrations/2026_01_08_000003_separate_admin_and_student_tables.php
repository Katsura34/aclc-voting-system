<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create admins table with minimal columns (security improvement)
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('name');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Create students table for voter data
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('usn')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('has_voted')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        // Migrate existing data from users table
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            if ($user->user_type === 'admin') {
                // Create admin record
                DB::table('admins')->insert([
                    'id' => $user->id,
                    'username' => $user->usn,
                    'name' => $user->name,
                    'password' => $user->password,
                    'remember_token' => $user->remember_token,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            } else {
                // Create student record
                DB::table('students')->insert([
                    'id' => $user->id,
                    'usn' => $user->usn,
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $user->password,
                    'has_voted' => $user->has_voted ?? false,
                    'remember_token' => $user->remember_token,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            }
        }

        // Update foreign key references in votes table to point to students
        // First, drop the existing foreign key constraint
        Schema::table('votes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Rename the column and add new foreign key
        Schema::table('votes', function (Blueprint $table) {
            $table->renameColumn('user_id', 'student_id');
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });

        // Update foreign key references in sessions table
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        // Drop the old users table
        Schema::dropIfExists('users');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('usn')->unique();
            $table->enum('user_type', ['student', 'admin'])->default('student');
            $table->boolean('has_voted')->default(false);
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Migrate data back from admins and students
        $admins = DB::table('admins')->get();
        foreach ($admins as $admin) {
            DB::table('users')->insert([
                'id' => $admin->id,
                'usn' => $admin->username,
                'user_type' => 'admin',
                'has_voted' => false,
                'name' => $admin->name,
                'email' => $admin->username . '@admin.aclc.edu.ph', // Placeholder email
                'password' => $admin->password,
                'remember_token' => $admin->remember_token,
                'created_at' => $admin->created_at,
                'updated_at' => $admin->updated_at,
            ]);
        }

        $students = DB::table('students')->get();
        foreach ($students as $student) {
            DB::table('users')->insert([
                'id' => $student->id,
                'usn' => $student->usn,
                'user_type' => 'student',
                'has_voted' => $student->has_voted,
                'name' => $student->name,
                'email' => $student->email,
                'password' => $student->password,
                'remember_token' => $student->remember_token,
                'created_at' => $student->created_at,
                'updated_at' => $student->updated_at,
            ]);
        }

        // Restore votes table foreign key
        Schema::table('votes', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->renameColumn('student_id', 'user_id');
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Restore sessions index
        Schema::table('sessions', function (Blueprint $table) {
            $table->index('user_id');
        });

        // Drop new tables
        Schema::dropIfExists('admins');
        Schema::dropIfExists('students');
    }
};
