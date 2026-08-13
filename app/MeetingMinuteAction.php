<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class MeetingMinuteAction extends Model
{
    protected $fillable = ['minute_id', 'task_id', 'assignee_id', 'title', 'due_date'];
    protected $casts = ['due_date' => 'date'];
    public function minute() { return $this->belongsTo(MeetingMinute::class, 'minute_id'); }
    public function task() { return $this->belongsTo(Task::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assignee_id'); }
}