<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class Worklog extends Model
{
    protected $fillable = ['task_id', 'user_id', 'assignee_id', 'hours', 'logged_at', 'started_at', 'ended_at', 'description'];

    protected $casts = [
        'logged_at' => 'date',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'hours' => 'decimal:2',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function getDurationHoursAttribute(): ?float
    {
        if ($this->started_at && $this->ended_at) {
            return round(abs($this->ended_at->diffInMinutes($this->started_at)) / 60, 2);
        }

        return $this->hours !== null ? (float) $this->hours : null;
    }
}