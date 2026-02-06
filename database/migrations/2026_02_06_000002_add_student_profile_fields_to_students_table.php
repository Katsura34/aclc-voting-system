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
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'firstname')) {
                $table->string('firstname')->nullable()->after('usn');
            }

            if (!Schema::hasColumn('students', 'lastname')) {
                $table->string('lastname')->nullable()->after('firstname');
            }

            if (!Schema::hasColumn('students', 'strand')) {
                $table->string('strand')->nullable()->after('lastname');
            }

            if (!Schema::hasColumn('students', 'year')) {
                $table->string('year')->nullable()->after('strand');
            }

            if (!Schema::hasColumn('students', 'gender')) {
                $table->string('gender')->nullable()->after('year');
            }

            if (Schema::hasColumn('students', 'email')) {
                $table->string('email')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            foreach (['firstname', 'lastname', 'strand', 'year', 'gender'] as $column) {
                if (Schema::hasColumn('students', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('students', 'email')) {
                $table->string('email')->nullable(false)->change();
            }
        });
    }
};
