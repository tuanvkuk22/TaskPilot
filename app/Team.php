<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'team_user')->withPivot('role', 'permissions');
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
