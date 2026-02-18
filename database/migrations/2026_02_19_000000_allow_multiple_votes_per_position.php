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
        Schema::table('votes', function (Blueprint $table) {
            // Drop the old unique index that prevented multiple candidate votes per position
            try {
                $table->dropUnique(['election_id', 'user_id', 'position_id']);
            } catch (\Exception $e) {
                // Index may not exist in some environments — ignore
            }

            // Add a new unique index that includes candidate_id so a user can vote for multiple candidates
            $table->unique(['election_id', 'user_id', 'position_id', 'candidate_id'], 'votes_election_user_position_candidate_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            try {
                $table->dropUnique('votes_election_user_position_candidate_unique');
            } catch (\Exception $e) {
                // ignore
            }

            $table->unique(['election_id', 'user_id', 'position_id']);
        });
    }
};
