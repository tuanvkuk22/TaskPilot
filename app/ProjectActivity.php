<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class ProjectActivity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'project_id', 'user_id', 'action', 'subject_type', 'subject_id',
        'subject_label', 'changes', 'meta', 'created_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }
}