<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Complete redesign for secure voting system with manual counting backup.
     */
    public function up(): void
    {
        // Drop existing voting-related tables to rebuild securely
        Schema::dropIfExists('votes');
        Schema::dropIfExists('voting_records');
        
        // =====================================================
        // VOTES TABLE - Anonymized voting records
        // =====================================================
        // Security: No direct link to student identity
        // Each vote has a unique hash for verification
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            $table->foreignId('position_id')->constrained()->onDelete('cascade');
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            
            // Security: Anonymous vote hash (cannot trace back to student)
            $table->string('vote_hash', 64)->unique()->comment('SHA256 hash for vote verification');
            
            // Security: Vote encryption key reference (for audit if needed)
            $table->string('encrypted_voter_id', 255)->comment('Encrypted student ID - only decryptable by admin with key');
            
            // Timestamp when vote was cast
            $table->timestamp('voted_at')->useCurrent();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['election_id', 'position_id', 'candidate_id']);
            $table->index('voted_at');
        });

        // =====================================================
        // VOTING_SESSIONS TABLE - Track who voted (not what they voted)
        // =====================================================
        // Purpose: Prevent double voting, enable manual counting
        Schema::create('voting_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            
            // Session information
            $table->string('session_token', 64)->unique()->comment('Unique session token');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            
            // Security: Track voting metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Status tracking
            $table->enum('status', ['started', 'completed', 'abandoned', 'invalid'])->default('started');
            
            // Manual counting support: Paper ballot reference
            $table->string('ballot_number', 20)->nullable()->unique()->comment('Physical ballot number for manual counting');
            
            $table->timestamps();
            
            // Ensure one session per student per election
            $table->unique(['election_id', 'student_id'], 'voting_sessions_unique');
            
            // Indexes
            $table->index('status');
            $table->index('completed_at');
            $table->index('ballot_number');
        });

        // =====================================================
        // VOTE_AUDIT_LOG TABLE - Immutable audit trail
        // =====================================================
        // Purpose: Track all voting activities for security auditing
        Schema::create('vote_audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            
            // What happened
            $table->enum('action', [
                'vote_cast',
                'vote_verified',
                'session_started',
                'session_completed',
                'session_abandoned',
                'suspicious_activity',
                'admin_access',
                'results_viewed'
            ]);
            
            // Who (can be student or admin)
            $table->string('actor_type', 20)->comment('student or admin');
            $table->unsignedBigInteger('actor_id')->nullable();
            
            // Details
            $table->text('details')->nullable()->comment('JSON data about the action');
            
            // Security metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Hash chain for tamper detection
            $table->string('previous_hash', 64)->nullable()->comment('Hash of previous log entry');
            $table->string('entry_hash', 64)->comment('Hash of this entry');
            
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index(['election_id', 'action']);
            $table->index('created_at');
            $table->index(['actor_type', 'actor_id']);
        });

        // =====================================================
        // MANUAL_COUNT_RECORDS TABLE - Manual counting backup
        // =====================================================
        // Purpose: Allow manual counting if system fails or for verification
        Schema::create('manual_count_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            $table->foreignId('position_id')->constrained()->onDelete('cascade');
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            
            // Manual count data
            $table->integer('manual_votes')->default(0)->comment('Manually counted votes');
            $table->integer('system_votes')->default(0)->comment('System counted votes');
            $table->integer('discrepancy')->default(0)->comment('Difference between manual and system');
            
            // Who performed the manual count
            $table->foreignId('counted_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('counted_at')->nullable();
            
            // Verification
            $table->boolean('verified')->default(false);
            $table->foreignId('verified_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable()->comment('Any notes about discrepancies');
            
            $table->timestamps();
            
            // Ensure one record per candidate per position per election
            $table->unique(['election_id', 'position_id', 'candidate_id'], 'manual_count_unique');
        });

        // =====================================================
        // VOTE_VERIFICATION_CODES TABLE - Voter receipt system
        // =====================================================
        // Purpose: Voters get a code to verify their vote was counted (not who they voted for)
        Schema::create('vote_verification_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            $table->foreignId('voting_session_id')->constrained('voting_sessions')->onDelete('cascade');
            
            // Verification code given to voter
            $table->string('verification_code', 20)->unique()->comment('Code given to voter as receipt');
            
            // What they can verify
            $table->integer('total_votes_cast')->comment('Number of votes they cast');
            $table->timestamp('voted_timestamp')->comment('When they voted');
            
            // Status
            $table->boolean('verified_by_voter')->default(false);
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('verification_code');
        });

        // =====================================================
        // ELECTION_INTEGRITY_CHECK TABLE - System health monitoring
        // =====================================================
        // Purpose: Regular integrity checks to detect tampering
        Schema::create('election_integrity_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            
            // Check details
            $table->timestamp('check_timestamp')->useCurrent();
            $table->integer('total_votes_expected')->comment('Based on voting sessions');
            $table->integer('total_votes_counted')->comment('Actual votes in database');
            $table->integer('total_students_voted')->comment('Unique students who voted');
            
            // Integrity status
            $table->boolean('integrity_passed')->default(true);
            $table->text('issues_found')->nullable()->comment('JSON array of issues');
            
            // Hash verification
            $table->string('database_hash', 64)->comment('Hash of all votes for this check');
            $table->string('previous_check_hash', 64)->nullable();
            
            // Performed by
            $table->foreignId('performed_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index('check_timestamp');
            $table->index('integrity_passed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('election_integrity_checks');
        Schema::dropIfExists('vote_verification_codes');
        Schema::dropIfExists('manual_count_records');
        Schema::dropIfExists('vote_audit_log');
        Schema::dropIfExists('voting_sessions');
        Schema::dropIfExists('votes');
    }
};
