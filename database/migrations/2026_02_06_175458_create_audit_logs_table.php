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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            $table->foreignId('position_id')->constrained()->onDelete('cascade');
            $table->foreignId('candidate_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('action_type')->default('vote_cast'); // vote_cast for now, can extend later
            $table->string('user_usn')->nullable(); // Store USN for reference
            $table->string('user_name')->nullable(); // Store user name for reference
            $table->string('candidate_name')->nullable(); // Store candidate name for reference
            $table->string('position_name')->nullable(); // Store position name for reference
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('voted_at')->useCurrent();
            $table->timestamps();
            
            // Index for faster queries
            $table->index('election_id');
            $table->index('user_id');
            $table->index(['election_id', 'voted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
