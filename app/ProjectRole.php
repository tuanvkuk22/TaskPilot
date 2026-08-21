<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class ProjectRole extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
