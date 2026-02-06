<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'name',
        'description',
        'max_votes',
        'display_order',
    ];

    protected $casts = [
        'max_votes' => 'integer',
        'display_order' => 'integer',
    ];

    /**
     * Get the elections that include this position.
     */
    public function elections()
    {
        return $this->belongsToMany(Election::class, 'election_position')
            ->withPivot('display_order')
            ->withTimestamps();
    }

    /**
     * Get the candidates for this position.
     */
    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }
}
