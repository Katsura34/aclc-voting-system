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
        Schema::table('users', function (Blueprint $table) {
            // Drop the old 'name' column if it exists
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }
            
            // Add new columns after 'usn' if it exists, otherwise after 'id'
            $afterColumn = Schema::hasColumn('users', 'usn') ? 'usn' : 'id';
            
            // Add firstname and lastname
            if (!Schema::hasColumn('users', 'firstname')) {
                $table->string('firstname')->after($afterColumn);
            }
            if (!Schema::hasColumn('users', 'lastname')) {
                $table->string('lastname')->after('firstname');
            }
            
            // Add strand (course/program)
            if (!Schema::hasColumn('users', 'strand')) {
                $table->string('strand')->nullable()->after('lastname');
            }
            
            // Add year
            if (!Schema::hasColumn('users', 'year')) {
                $table->string('year')->nullable()->after('strand');
            }
            
            // Add gender (correcting the typo "gander")
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable()->after('year');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn(['firstname', 'lastname', 'strand', 'year', 'gender']);
            
            // Restore old name column
            $table->string('name')->after('id');
        });
    }
};
