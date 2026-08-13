<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class ProjectPermission extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'roles',
    ];

    protected $casts = [
        'roles' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
