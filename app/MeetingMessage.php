<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class MeetingMessage extends Model
{
    protected $fillable = ['meeting_id', 'user_id', 'recipient_id', 'source_task_id', 'body', 'attachment_path', 'attachment_name', 'attachment_mime', 'attachment_size'];
    protected $casts = ['edited_at' => 'datetime', 'deleted_at' => 'datetime'];
    public function meeting() { return $this->belongsTo(Meeting::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function recipient() { return $this->belongsTo(User::class, 'recipient_id'); }
    public function mentions() { return $this->belongsToMany(User::class, 'meeting_message_mentions', 'message_id'); }
    public function sourceTask() { return $this->belongsTo(Task::class, 'source_task_id'); }
    public function attachments() { return $this->hasMany(MeetingMessageAttachment::class, 'message_id'); }
}