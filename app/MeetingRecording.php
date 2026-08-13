<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class MeetingRecording extends Model
{
    protected $fillable = ['meeting_id', 'project_id', 'uploaded_by', 'path', 'original_name', 'mime_type', 'size'];
    public function meeting() { return $this->belongsTo(Meeting::class); }
    public function project() { return $this->belongsTo(Project::class); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
}