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
        // Remove is_abstain column from votes table
        Schema::table('votes', function (Blueprint $table) {
            $table->dropColumn('is_abstain');
        });

        // Remove allow_abstain column from elections table
        Schema::table('elections', function (Blueprint $table) {
            $table->dropColumn('allow_abstain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore is_abstain column to votes table
        Schema::table('votes', function (Blueprint $table) {
            $table->boolean('is_abstain')->default(false)->after('candidate_id');
        });

        // Restore allow_abstain column to elections table
        Schema::table('elections', function (Blueprint $table) {
            $table->boolean('allow_abstain')->default(true)->after('end_date');
        });
    }
};
