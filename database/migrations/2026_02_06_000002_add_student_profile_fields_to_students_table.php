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
            $table->string('first_name')->nullable()->after('usn');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('strand')->nullable()->after('last_name');
            $table->string('year')->nullable()->after('strand');
            $table->string('gender')->nullable()->after('year');
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'strand', 'year', 'gender']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
