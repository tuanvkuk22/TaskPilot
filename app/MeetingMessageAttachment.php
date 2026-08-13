<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class MeetingMessageAttachment extends Model
{
    protected $fillable = ['message_id', 'path', 'name', 'mime', 'size'];

    public function message()
    {
        return $this->belongsTo(MeetingMessage::class, 'message_id');
    }
}