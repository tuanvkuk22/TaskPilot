<?php

namespace ProjectsManager;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected static function newFactory(): \Database\Factories\UserFactory
    {
        return \Database\Factories\UserFactory::new();
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 
        'email', 
        'password',
        'first_name',
        'middle_name',
        'last_name',
        'city',
        'role_id',
        'avatar',
        'provider',
        'provider_id',
        'status',
        'last_login_at',
        'username',
        'full_name',
        'job_title',
        'department',
        'timezone',
        'email_notifications',
        'in_app_notifications',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

  
    
    public function role(){
		return $this->belongsTo('ProjectsManager\Role');
    }

    public function hasRoleName(string $name): bool
    {
        return $this->role && $this->role->name === $name;
    }

    public function companies(){
		return $this->hasMany('ProjectsManager\Company');
    }
    

    public function tasks()
    {
        return $this->belongsToMany('ProjectsManager\Task');
    }


    //many to many relationship
    public function projects()
    {
        return $this->belongsToMany('ProjectsManager\Project');
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    public function comments()
    {
        return $this->morphMany('ProjectsManager\Comment', 'commentable');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_user')->withPivot('role', 'permissions');
    }

    public function ownedTeams()
    {
        return $this->hasMany(Team::class, 'user_id');
    }

    public function skills()
    {
        return $this->hasMany(UserSkill::class, 'user_id');
    }

    public function aiRecommendations()
    {
        return $this->hasMany(AiAssigneeRecommendation::class, 'user_id');
    }
}
