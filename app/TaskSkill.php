<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class TaskSkill extends Model
{
    protected $table = 'task_skills';

    protected $fillable = [
        'task_id',
        'skill_name',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
