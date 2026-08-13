<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = ['project_id', 'team_id', 'created_by', 'title', 'description', 'room_name', 'status', 'starts_at', 'ended_at'];

    protected $casts = ['starts_at' => 'datetime', 'ended_at' => 'datetime'];

    public function project() { return $this->belongsTo(Project::class); }
    public function team() { return $this->belongsTo(Team::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function members() { return $this->belongsToMany(User::class, 'meeting_members')->withPivot('role', 'permissions')->withTimestamps(); }
    public function messages() { return $this->hasMany(MeetingMessage::class); }
    public function note() { return $this->hasOne(MeetingNote::class); }
    public function minutes() { return $this->hasOne(MeetingMinute::class); }
    public function recordings() { return $this->hasMany(MeetingRecording::class); }
    public function participantLogs() { return $this->hasMany(MeetingParticipantLog::class); }
    public function documents() { return $this->hasMany(MeetingDocument::class); }
    public function breakoutRooms() { return $this->hasMany(MeetingBreakoutRoom::class); }
}