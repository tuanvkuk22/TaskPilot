<?php

namespace ProjectsManager\Observers;

use ProjectsManager\Services\ActivityLogger;
use ProjectsManager\Sprint;

class SprintObserver
{
    public function created(Sprint $sprint): void
    {
        if (! $sprint->project_id) {
            return;
        }

        ActivityLogger::record(
            projectId: $sprint->project_id,
            action: ActivityLogger::ACTION_CREATED,
            userId: auth()->id(),
            subject: $sprint,
            meta: 'sprint',
        );
    }

    public function updated(Sprint $sprint): void
    {
        if (! $sprint->project_id) {
            return;
        }

        $changes = $sprint->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        ActivityLogger::record(
            projectId: $sprint->project_id,
            action: ActivityLogger::ACTION_UPDATED,
            userId: auth()->id(),
            subject: $sprint,
            changes: $changes,
        );
    }
}