<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
    protected $fillable = [
        'name',
        'project_id',
        'user_id',
        'days',
        'hours',
        'company_id', 'description', 'status', 'priority', 'issue_type', 'assignee_id', 'reporter_id',
        'due_date', 'start_date', 'end_date', 'story_points', 'labels', 'sprint', 'parent_id', 'sprint_id',
    ];

    protected $casts = ['due_date' => 'date', 'start_date' => 'date', 'end_date' => 'date', 'story_points' => 'decimal:1'];


    public function user(){
		return $this->belongsTo(User::class);
    }

    public function project(){
		return $this->belongsTo(Project::class);
    }

    public function company(){
		return $this->belongsTo(Company::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function comments()
    {
        return $this->morphMany('ProjectsManager\Comment', 'commentable');
    }

    public function assignee() { return $this->belongsTo(User::class, 'assignee_id'); }
    public function reporter() { return $this->belongsTo(User::class, 'reporter_id'); }
    public function sprintRelation() { return $this->belongsTo(Sprint::class, 'sprint_id'); }
    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }
    public function children() { return $this->hasMany(self::class, 'parent_id'); }
    public function worklogs() { return $this->hasMany(Worklog::class); }
}
