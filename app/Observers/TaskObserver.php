<?php

namespace ProjectsManager\Observers;

use ProjectsManager\Services\ActivityLogger;
use ProjectsManager\Task;

class TaskObserver
{
    public function created(Task $task): void
    {
        if (! $task->project_id) {
            return;
        }

        ActivityLogger::record(
            projectId: $task->project_id,
            action: ActivityLogger::ACTION_CREATED,
            userId: $task->user_id,
            subject: $task,
            meta: $task->issue_type,
        );
    }

    public function updated(Task $task): void
    {
        if (! $task->project_id) {
            return;
        }

        $changes = $task->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $original = $task->getOriginal();

        $humanChanges = [];
        foreach ($changes as $field => $newValue) {
            $humanChanges[$field] = [
                'old' => $original[$field] ?? null,
                'new' => $newValue,
            ];
        }

        ActivityLogger::record(
            projectId: $task->project_id,
            action: ActivityLogger::ACTION_UPDATED,
            userId: auth()->id(),
            subject: $task,
            changes: $humanChanges,
        );
    }

    public function deleted(Task $task): void
    {
        if (! $task->project_id) {
            return;
        }

        ActivityLogger::record(
            projectId: $task->project_id,
            action: ActivityLogger::ACTION_DELETED,
            userId: auth()->id(),
            subjectLabel: $task->name,
        );
    }
}