<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class MeetingDocument extends Model
{
    public const TYPE_FILE = 'file';
    public const TYPE_LINK = 'link';

    protected $fillable = ['meeting_id', 'user_id', 'type', 'name', 'path', 'url', 'mime', 'size'];

    public function meeting() { return $this->belongsTo(Meeting::class); }
    public function user() { return $this->belongsTo(User::class); }
}
