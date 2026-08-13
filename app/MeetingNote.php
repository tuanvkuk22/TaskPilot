<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class MeetingNote extends Model
{
    protected $fillable = ['meeting_id', 'user_id', 'body'];
    public function meeting() { return $this->belongsTo(Meeting::class); }
    public function user() { return $this->belongsTo(User::class); }
}