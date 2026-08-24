<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class ProjectDevelopmentLink extends Model
{
    protected $table = 'project_development_links';

    protected $fillable = ['project_id', 'user_id', 'type', 'label', 'url', 'status', 'linked_at'];

    protected $casts = ['linked_at' => 'datetime'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
