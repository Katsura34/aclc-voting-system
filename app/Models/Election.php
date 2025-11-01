<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Election extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'is_active',
        'allow_abstain',
        'show_live_results',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'allow_abstain' => 'boolean',
        'show_live_results' => 'boolean',
    ];

    /**
     * Get the positions for this election.
     */
    public function positions()
    {
        return $this->hasMany(Position::class)->orderBy('display_order');
    }

    /**
     * Get the candidates for this election through positions.
     */
    public function candidates()
    {
        return $this->hasManyThrough(Candidate::class, Position::class);
    }
}
