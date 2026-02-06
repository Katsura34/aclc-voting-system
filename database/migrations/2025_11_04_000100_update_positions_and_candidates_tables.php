<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            if (!Schema::hasColumn('candidates', 'election_id')) {
                $table->foreignId('election_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->onDelete('cascade');
            }
        });

        // Backfill election_id from positions table before removing the column
        if (Schema::hasColumn('positions', 'election_id')) {
            DB::table('candidates')
                ->join('positions', 'candidates.position_id', '=', 'positions.id')
                ->whereNotNull('positions.election_id')
                ->update(['candidates.election_id' => DB::raw('positions.election_id')]);

            Schema::table('positions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('election_id');
            });
        }

        // Make election_id mandatory after backfill
        Schema::table('candidates', function (Blueprint $table) {
            $table->foreignId('election_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            if (Schema::hasColumn('candidates', 'election_id')) {
                $table->dropConstrainedForeignId('election_id');
            }
        });

        Schema::table('positions', function (Blueprint $table) {
            if (!Schema::hasColumn('positions', 'election_id')) {
                $table->foreignId('election_id')
                    ->after('id')
                    ->constrained()
                    ->onDelete('cascade');
            }
        });
    }
};
