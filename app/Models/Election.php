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
        $query = Candidate::whereIn('position_id', $positionIds);

        $partyIds = $this->parties()->pluck('parties.id');
        if ($partyIds->isNotEmpty()) {
            $query->whereIn('party_id', $partyIds);
        }

        return $query;
    }

    /**
     * Get the active election with caching for better performance.
     * Cache is cleared when election status changes.
     */
    public static function getActiveElection()
    {
        return Cache::remember('active_election', 300, function () {
            $election = self::where('is_active', true)
                ->with([
                    'positions' => function ($query) {
                        $query->orderBy('election_position.display_order');
                    },
                    'positions.candidates.party',
                    'parties',
                ])
                ->first();

            if ($election) {
                $election->filterCandidatesToParticipatingParties();
            }

            return $election;
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
     * Remove candidates that belong to parties not participating in this election.
     */
    public function filterCandidatesToParticipatingParties(): void
    {
        if (!$this->relationLoaded('positions')) {
            return;
        }

        $allowedPartyIds = $this->parties->pluck('id')->filter()->values();
        if ($allowedPartyIds->isEmpty()) {
            return;
        }

        $this->positions->each(function ($position) use ($allowedPartyIds) {
            if (!$position->relationLoaded('candidates')) {
                return;
            }

            $filtered = $position->candidates
                ->whereIn('party_id', $allowedPartyIds)
                ->values();

            $position->setRelation('candidates', $filtered);
        });
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
