<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'position_id',
        'party_id',
        'course',
        'year_level',
        'bio',
        'photo_path',
    ];

    /**
     * Get the position this candidate is running for.
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the party this candidate belongs to.
     */
    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    /**
     * Get the votes for this candidate.
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get the full name of the candidate.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
