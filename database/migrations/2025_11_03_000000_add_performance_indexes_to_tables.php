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
        // Add indexes to elections table for faster queries
        Schema::table('elections', function (Blueprint $table) {
            $table->index('is_active');
            $table->index(['start_date', 'end_date']);
        });

        // Add indexes to positions table for faster queries
        Schema::table('positions', function (Blueprint $table) {
            $table->index('election_id');
            $table->index('display_order');
        });

        // Add indexes to candidates table for faster queries
        Schema::table('candidates', function (Blueprint $table) {
            $table->index('position_id');
            $table->index('party_id');
            $table->index(['position_id', 'party_id']);
        });

        // Add indexes to votes table for faster queries and analytics
        Schema::table('votes', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('election_id');
            $table->index('position_id');
            $table->index('candidate_id');
            $table->index(['election_id', 'position_id']);
            $table->index(['position_id', 'candidate_id']);
        });

        // Add indexes to users table for faster lookups
        Schema::table('users', function (Blueprint $table) {
            $table->index('user_type');
            $table->index('has_voted');
            $table->index(['user_type', 'has_voted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['start_date', 'end_date']);
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->dropIndex(['election_id']);
            $table->dropIndex(['display_order']);
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropIndex(['position_id']);
            $table->dropIndex(['party_id']);
            $table->dropIndex(['position_id', 'party_id']);
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['election_id']);
            $table->dropIndex(['position_id']);
            $table->dropIndex(['candidate_id']);
            $table->dropIndex(['election_id', 'position_id']);
            $table->dropIndex(['position_id', 'candidate_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['user_type']);
            $table->dropIndex(['has_voted']);
            $table->dropIndex(['user_type', 'has_voted']);
        });
    }
};
