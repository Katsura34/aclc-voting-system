<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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

    /**
     * Get the active election with caching for better performance.
     * Cache is cleared when election status changes.
     */
    public static function getActiveElection()
    {
        return Cache::remember('active_election', 300, function () {
            return self::where('is_active', true)
                ->with([
                    'positions' => function ($query) {
                        $query->orderBy('display_order');
                    },
                    'positions.candidates.party'
                ])
                ->first();
        });
    }

    /**
     * Clear active election cache.
     */
    public static function clearActiveElectionCache()
    {
        Cache::forget('active_election');
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        // Clear cache when election is updated or deleted
        static::saved(function () {
            self::clearActiveElectionCache();
        });

        static::deleted(function () {
            self::clearActiveElectionCache();
        });
    }
}
