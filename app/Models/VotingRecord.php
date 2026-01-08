<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VotingRecord extends Model
{
    protected $fillable = [
        'election_id',
        'student_id',
        'voted_at',
        'ip_address',
    ];

    protected $casts = [
        'voted_at' => 'datetime',
    ];

    /**
     * Get the election this record belongs to.
     */
    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * Get the student who voted.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
