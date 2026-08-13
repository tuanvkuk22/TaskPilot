<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class MeetingBreakoutRoom extends Model
{
    protected $fillable = ['meeting_id', 'task_id', 'created_by', 'name', 'room_name', 'closed_at'];

    protected $casts = ['closed_at' => 'datetime'];

    public function meeting() { return $this->belongsTo(Meeting::class); }
    public function task() { return $this->belongsTo(Task::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function members() { return $this->belongsToMany(User::class, 'meeting_breakout_room_members', 'breakout_room_id', 'user_id')->withTimestamps(); }
}
