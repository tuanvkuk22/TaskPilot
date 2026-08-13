<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    //

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'contact_phone',
        'contact_email',
        'contact_address',
        'contact_note',

    ];

    public function user(){
		return $this->belongsTo(User::class);
    }

    public function projects(){
        return $this->hasMany(Project::class);
    }

    public function comments()
    {
        return $this->morphMany('ProjectsManager\Comment', 'commentable');
    }
}
