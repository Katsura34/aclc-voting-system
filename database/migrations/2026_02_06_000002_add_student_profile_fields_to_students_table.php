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
            $table->string('firstname')->nullable()->after('usn');
            $table->string('lastname')->nullable()->after('firstname');
            $table->string('strand')->nullable()->after('lastname');
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
            $table->dropColumn(['firstname', 'lastname', 'strand', 'year', 'gender']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
