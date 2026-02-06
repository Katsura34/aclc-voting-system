<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create election_position pivot table
        Schema::create('election_position', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            $table->foreignId('position_id')->constrained()->onDelete('cascade');
            $table->integer('display_order')->default(0);
            $table->timestamps();
            $table->unique(['election_id', 'position_id']);
        });

        // Migrate existing position-election relationships to pivot table
        $positions = DB::table('positions')->whereNotNull('election_id')->get();
        foreach ($positions as $position) {
            DB::table('election_position')->insert([
                'election_id' => $position->election_id,
                'position_id' => $position->id,
                'display_order' => $position->display_order ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Make election_id nullable on positions table
        // Handle SQLite by recreating the table
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=off;');

            Schema::create('positions_new', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('election_id')->nullable();
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('max_votes')->default(1);
                $table->integer('display_order')->default(0);
                $table->timestamps();
            });

            DB::statement('INSERT INTO positions_new (id, election_id, name, description, max_votes, display_order, created_at, updated_at) SELECT id, election_id, name, description, max_votes, display_order, created_at, updated_at FROM positions');

            Schema::drop('positions');
            Schema::rename('positions_new', 'positions');

            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            Schema::table('positions', function (Blueprint $table) {
                $table->unsignedBigInteger('election_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Restore election_id from pivot data
        $pivotData = DB::table('election_position')->get();
        foreach ($pivotData as $pivot) {
            DB::table('positions')
                ->where('id', $pivot->position_id)
                ->update(['election_id' => $pivot->election_id]);
        }

        Schema::dropIfExists('election_position');

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=off;');

            Schema::create('positions_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('election_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('max_votes')->default(1);
                $table->integer('display_order')->default(0);
                $table->timestamps();
            });

            DB::statement('INSERT INTO positions_new (id, election_id, name, description, max_votes, display_order, created_at, updated_at) SELECT id, election_id, name, description, max_votes, display_order, created_at, updated_at FROM positions');

            Schema::drop('positions');
            Schema::rename('positions_new', 'positions');

            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            Schema::table('positions', function (Blueprint $table) {
                $table->unsignedBigInteger('election_id')->nullable(false)->change();
            });
        }
    }
};
