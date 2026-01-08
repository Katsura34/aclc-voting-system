<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates a voting record table for manual counting backup.
     * Records which students voted (not who they voted for) for election integrity.
     */
    public function up(): void
    {
        Schema::create('voting_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->timestamp('voted_at')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            
            // Ensure one record per student per election
            $table->unique(['election_id', 'student_id']);
            
            // Index for quick lookups
            $table->index('voted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_records');
    }
};
