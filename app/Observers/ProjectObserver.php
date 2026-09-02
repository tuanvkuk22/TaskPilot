<?php

namespace ProjectsManager\Observers;

use ProjectsManager\Project;
use ProjectsManager\Services\ActivityLogger;

class ProjectObserver
{
    public function updated(Project $project): void
    {
        $changes = $project->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        ActivityLogger::record(
            projectId: $project->getKey(),
            action: ActivityLogger::ACTION_UPDATED,
            userId: auth()->id(),
            subject: $project,
            changes: $changes,
        );
    }

    public function archived(Project $project): void
    {
        ActivityLogger::record(
            projectId: $project->getKey(),
            action: ActivityLogger::ACTION_ARCHIVED,
            userId: auth()->id(),
            subject: $project,
        );
    }
}