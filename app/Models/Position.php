<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'election_id',
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
     * Get the election that owns the position.
     */
    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * Get the candidates for this position.
     */
    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }
}
