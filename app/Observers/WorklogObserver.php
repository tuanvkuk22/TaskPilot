<?php

namespace ProjectsManager\Observers;

use ProjectsManager\Services\ActivityLogger;
use ProjectsManager\Services\NotificationService;
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

        $task = $worklog->task;
        if (! $task) {
            return;
        }

        $project = $task->project;
        if (! $project) {
            return;
        }

        $actor = \ProjectsManager\User::find($worklog->user_id);

        $project->load('users');

        if ($project->users->isNotEmpty()) {
            NotificationService::sendToProject(
                $project,
                NotificationService::EVENT_WORKLOG_CREATED,
                'Ghi nhận thời gian: ' . $task->name,
                $actor?->name . ' đã ghi nhận ' . $worklog->hours . 'h cho công việc "' . $task->name . '".',
                $actor,
                ['hours' => $worklog->hours, 'task_name' => $task->name, 'issue_key' => $task->issue_key ?? null],
                '/tasks/' . $task->id,
            );
        }
    }
}
