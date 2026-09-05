<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class AiAssigneeRecommendation extends Model
{
    protected $table = 'ai_assignee_recommendations';

    protected $fillable = [
        'task_id',
        'user_id',
        'score',
        'rank_position',
        'match_reasons',
        'status',
    ];

    protected $casts = [
        'match_reasons' => 'array',
        'score' => 'float',
        'rank_position' => 'integer',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
