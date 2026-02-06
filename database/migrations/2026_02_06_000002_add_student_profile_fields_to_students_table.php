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
            $anchor = 'usn';

            if (Schema::hasColumn('students', 'firstname')) {
                $anchor = 'firstname';
            } else {
                $table->string('firstname')->nullable()->after($anchor);
                $anchor = 'firstname';
            }

            if (Schema::hasColumn('students', 'lastname')) {
                $anchor = 'lastname';
            } else {
                $table->string('lastname')->nullable()->after($anchor);
                $anchor = 'lastname';
            }

            if (Schema::hasColumn('students', 'strand')) {
                $anchor = 'strand';
            } else {
                $table->string('strand')->nullable()->after($anchor);
                $anchor = 'strand';
            }

            if (Schema::hasColumn('students', 'year')) {
                $anchor = 'year';
            } else {
                $table->string('year')->nullable()->after($anchor);
                $anchor = 'year';
            }

            if (! Schema::hasColumn('students', 'gender')) {
                $table->string('gender')->nullable()->after($anchor);
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
