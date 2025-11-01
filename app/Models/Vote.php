<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = [
        'user_id',
        'election_id',
        'position_id',
        'candidate_id',
    ];

    /**
     * Get the user who cast the vote.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the election this vote belongs to.
     */
    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * Get the position this vote is for.
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the candidate who received the vote.
     */
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
