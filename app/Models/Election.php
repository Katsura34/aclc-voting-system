<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Models\Candidate;

class Election extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the positions for this election.
     */
    public function positions()
    {
        return $this->belongsToMany(Position::class, 'election_position')
            ->withPivot('display_order')
            ->withTimestamps()
            ->orderBy('election_position.display_order');
    }

    /**
     * Get the parties for this election.
     */
    public function parties()
    {
        return $this->belongsToMany(Party::class, 'election_party')
            ->withTimestamps();
    }

    /**
     * Get a query builder for candidates belonging to this election's positions.
     * Note: This is not a standard Eloquent relationship, use ->get() or ->count() on the result.
     * For eager loading candidates, use 'positions.candidates' instead.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function candidates()
    {
        $positionIds = $this->positions()->pluck('positions.id');
        return Candidate::whereIn('position_id', $positionIds);
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
                        $query->orderBy('election_position.display_order');
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
