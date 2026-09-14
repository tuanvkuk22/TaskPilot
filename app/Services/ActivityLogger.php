<?php

namespace ProjectsManager\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use ProjectsManager\ProjectActivity;

class ActivityLogger
{
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_INVITED = 'invited';
    public const ACTION_REMOVED = 'removed';
    public const ACTION_COMMENTED = 'commented';
    public const ACTION_COMMENT_EDITED = 'comment_edited';
    public const ACTION_COMMENT_DELETED = 'comment_deleted';
    public const ACTION_LOGGED_TIME = 'logged_time';
    public const ACTION_ARCHIVED = 'archived';
    public const ACTION_RESTORED = 'restored';

    public static function record(
        int $projectId,
        string $action,
        ?int $userId = null,
        ?Model $subject = null,
        ?array $changes = null,
        ?string $meta = null,
        ?string $subjectLabel = null,
    ): ProjectActivity {
        return ProjectActivity::create([
            'project_id' => $projectId,
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'subject_label' => $subjectLabel ?? ($subject ? self::labelFor($subject) : null),
            'changes' => $changes,
            'meta' => $meta,
            'created_at' => now(),
        ]);
    }

    public static function labelFor(Model $model): string
    {
        return match (true) {
            $model instanceof \ProjectsManager\Task => $model->name ?? ('Task #' . $model->getKey()),
            $model instanceof \ProjectsManager\Comment => 'Comment #' . $model->getKey(),
            $model instanceof \ProjectsManager\Worklog => 'Worklog #' . $model->getKey(),
            $model instanceof \ProjectsManager\Sprint => $model->name ?? ('Sprint #' . $model->getKey()),
            $model instanceof \ProjectsManager\User => $model->name ?? ('User #' . $model->getKey()),
            $model instanceof \ProjectsManager\Project => $model->name ?? ('Project #' . $model->getKey()),
            default => class_basename($model) . ' #' . $model->getKey(),
        };
    }
}
