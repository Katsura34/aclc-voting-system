<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Models\Position;

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

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }

    public function positions()
    {
        return $this->belongsToMany(Position::class, Candidate::class, 'election_id', 'position_id')
            ->withPivot('id')
            ->orderBy('positions.display_order')
            ->distinct();
    }

    /**
     * Get the active election with caching for better performance.
     * Cache is cleared when election status changes.
     */
    public static function getActiveElection()
    {
        return Cache::remember('active_election', 300, function () {
            $election = self::where('is_active', true)->first();

            if (!$election) {
                return null;
            }

            $election->load(['candidates.party', 'candidates.position']);

            $positions = Position::whereHas('candidates', function ($query) use ($election) {
                    $query->where('election_id', $election->id);
                })
                ->with(['candidates' => function ($query) use ($election) {
                    $query->where('election_id', $election->id)
                        ->with('party');
                }])
                ->orderBy('display_order')
                ->get();

            $election->setRelation('positions', $positions);

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
