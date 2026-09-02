<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    //
    protected $fillable = [
        'name',
        'image',
        'description',
        'company_id',
        'user_id',
        'days', 'status', 'archived', 'project_type', 'team_name', 'project_code',
        'start_date', 'end_date', 'budget', 'priority', 'customer_name', 'location',
        'objective', 'category_id', 'team_id',
    ];

    protected $casts = ['archived' => 'boolean', 'start_date' => 'date', 'end_date' => 'date', 'budget' => 'decimal:2'];


    public function users(){
		return $this->belongsToMany(User::class)->withPivot('role', 'permissions');
    }

    public function roles(){
        return $this->hasMany(ProjectRole::class);
    }

    public function company(){
		return $this->belongsTo(Company::class);
    }

    public function comments()
    {
        return $this->morphMany('ProjectsManager\Comment', 'commentable');
    }

    public function tasks() { return $this->hasMany(Task::class); }
    public function category() { return $this->belongsTo(ProjectCategory::class); }
    public function team() { return $this->belongsTo(Team::class); }
    public function sprints() { return $this->hasMany(Sprint::class); }
    public function permissions() { return $this->hasMany(ProjectPermission::class); }
    public function meetings() { return $this->hasMany(Meeting::class); }
    public function documents() { return $this->hasMany(ProjectDocument::class); }
    public function forms() { return $this->hasMany(ProjectForm::class); }
    public function developmentLinks() { return $this->hasMany(ProjectDevelopmentLink::class); }
    public function activities() { return $this->hasMany(ProjectActivity::class)->latest('created_at'); }

    public function scopeStatusFilter($query, $status)
    {
        if (empty($status) || $status === 'all') {
            return $query;
        }

        return $query->where('status', $status);
    }
}
