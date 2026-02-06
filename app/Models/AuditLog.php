<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'election_id',
        'position_id',
        'candidate_id',
        'action_type',
        'user_usn',
        'user_name',
        'candidate_name',
        'position_name',
        'ip_address',
        'user_agent',
        'voted_at',
    ];

    protected $casts = [
        'voted_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the election this audit log belongs to.
     */
    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * Get the position this audit log is for.
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the candidate in this audit log.
     */
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
