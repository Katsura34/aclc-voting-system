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
     * Get the candidates for this position.
     */
    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }
}
