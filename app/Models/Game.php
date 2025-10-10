<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * @var array<int, string>
     */
    protected $fillable = [
        'rawg_id',
        'name',
        'slug',
        'released',
        'background_image',
        'rating',
        'metacritic',
    ];

    /**
     * The users that have this game in their lists.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'game_user')
            ->withPivot('list_status')
            ->withTimestamps();
    }
}
