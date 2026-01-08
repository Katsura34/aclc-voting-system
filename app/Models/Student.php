<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'usn',
        'name',
        'email',
        'password',
        'has_voted',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'has_voted' => 'boolean',
        ];
    }

    /**
     * Get the votes cast by this student.
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get the name of the password attribute.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the column name for the "username".
     *
     * @return string
     */
    public function username()
    {
        return 'usn';
    }
}
