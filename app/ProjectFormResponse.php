<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class ProjectFormResponse extends Model
{
    protected $fillable = ['form_id', 'user_id', 'data'];

    protected $casts = ['data' => 'array'];

    public function form()
    {
        return $this->belongsTo(ProjectForm::class, 'form_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
