<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class Worklog extends Model
{
    protected $fillable = ['task_id', 'user_id', 'hours', 'logged_at', 'description'];

    protected $casts = ['logged_at' => 'date', 'hours' => 'decimal:2'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}