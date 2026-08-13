<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class MeetingParticipantLog extends Model
{
    public const JOINED = 'joined';
    public const LEFT = 'left';

    protected $fillable = ['meeting_id', 'user_id', 'event'];

    public function meeting() { return $this->belongsTo(Meeting::class); }
    public function user() { return $this->belongsTo(User::class); }
}
