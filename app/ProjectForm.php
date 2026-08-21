<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class ProjectForm extends Model
{
    protected $fillable = ['project_id', 'user_id', 'name', 'description', 'fields', 'active'];

    protected $casts = ['fields' => 'array', 'active' => 'boolean'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function responses()
    {
        return $this->hasMany(ProjectFormResponse::class, 'form_id');
    }
}
