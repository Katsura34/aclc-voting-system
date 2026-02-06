<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    protected $fillable = [
        'name',
        'acronym',
        'color',
        'logo',
        'description',
    ];

    /**
     * Get the candidates belonging to this party.
     */
    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }

    /**
     * Get the elections that include this party.
     */
    public function elections()
    {
        return $this->belongsToMany(Election::class, 'election_party')
            ->withTimestamps();
    }
}
