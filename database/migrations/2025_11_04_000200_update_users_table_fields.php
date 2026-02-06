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
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('users', 'email')) {
                $table->dropUnique(['email']);
                $table->dropColumn(['email', 'email_verified_at']);
            }

            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->after('id');
            }
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->after('first_name');
            }
            if (!Schema::hasColumn('users', 'strand')) {
                $table->string('strand')->nullable()->after('last_name');
            }
            if (Schema::hasColumn('users', 'usn')) {
                $table->string('usn')->unique()->change();
            } else {
                $table->string('usn')->unique()->after('strand');
            }
            if (!Schema::hasColumn('users', 'year')) {
                $table->string('year')->nullable()->after('usn');
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable()->after('year');
            }
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->enum('user_type', ['student', 'admin'])->default('student')->after('gender');
            } else {
                $table->enum('user_type', ['student', 'admin'])->default('student')->change();
            }
            if (!Schema::hasColumn('users', 'has_voted')) {
                $table->boolean('has_voted')->default(false)->after('user_type');
            } else {
                $table->boolean('has_voted')->default(false)->change();
            }
            $table->string('password')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'strand', 'year', 'gender']);
            $table->dropUnique(['usn']);
            $table->dropColumn('usn');
            $table->dropColumn(['user_type', 'has_voted']);
            $table->string('name')->after('id');
            $table->string('email')->unique()->after('name');
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });
    }
};
