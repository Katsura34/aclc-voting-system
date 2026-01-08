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
        // Remove unused columns from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_verified_at');
        });

        // Remove show_live_results column from elections table (minimal usage)
        Schema::table('elections', function (Blueprint $table) {
            $table->dropColumn('show_live_results');
        });

        // Drop password_reset_tokens table (not used - no password reset functionality)
        Schema::dropIfExists('password_reset_tokens');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore email_verified_at to users table
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });

        // Restore show_live_results to elections table
        Schema::table('elections', function (Blueprint $table) {
            $table->boolean('show_live_results')->default(false)->after('end_date');
        });

        // Restore password_reset_tokens table
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }
};
