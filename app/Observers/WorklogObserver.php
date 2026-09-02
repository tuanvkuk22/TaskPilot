<?php

namespace ProjectsManager\Observers;

use ProjectsManager\Services\ActivityLogger;
use ProjectsManager\Worklog;

class WorklogObserver
{
    public function created(Worklog $worklog): void
    {
        $projectId = $worklog->task?->project_id;
        if (! $projectId) {
            return;
        }

        ActivityLogger::record(
            projectId: $projectId,
            action: ActivityLogger::ACTION_LOGGED_TIME,
            userId: $worklog->user_id,
            subject: $worklog,
            meta: sprintf('%sh • %s', $worklog->hours, $worklog->task?->name ?? ''),
        );
    }
}