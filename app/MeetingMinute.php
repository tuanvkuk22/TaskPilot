<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class MeetingMinute extends Model
{
    protected $fillable = ['meeting_id', 'created_by', 'content', 'decisions'];
    public function meeting() { return $this->belongsTo(Meeting::class); }
    public function actions() { return $this->hasMany(MeetingMinuteAction::class, 'minute_id'); }
}