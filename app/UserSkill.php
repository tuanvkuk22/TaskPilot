<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class UserSkill extends Model
{
    protected $table = 'user_skills';

    protected $fillable = [
        'user_id',
        'skill_name',
        'proficiency_level',
        'years_experience',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
