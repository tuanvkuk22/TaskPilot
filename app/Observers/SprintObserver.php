<?php

namespace ProjectsManager\Observers;

use ProjectsManager\Services\ActivityLogger;
use ProjectsManager\Services\NotificationService;
use ProjectsManager\Sprint;
use ProjectsManager\User;

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

        $project = $sprint->project;
        if (! $project) {
            return;
        }

        $actor = User::find(auth()->id());

        $project->load('users');

        if ($project->users->isNotEmpty()) {
            NotificationService::sendToProject(
                $project,
                NotificationService::EVENT_SPRINT_CREATED,
                'Sprint mới: ' . $sprint->name,
                $actor?->name . ' đã tạo sprint "' . $sprint->name . '" trong dự án "' . $project->name . '".',
                $actor,
                ['sprint_name' => $sprint->name, 'project' => $project->name],
                '/projects/' . $project->id . '/sprints',
            );
        }
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

        $project = $sprint->project;
        if (! $project) {
            return;
        }

        $actor = User::find(auth()->id());
        $project->load('users');

        $changedFields = array_keys($changes);
        $changedFieldsText = implode(', ', $changedFields);

        if ($project->users->isNotEmpty()) {
            NotificationService::sendToProject(
                $project,
                NotificationService::EVENT_SPRINT_UPDATED,
                'Sprint được cập nhật: ' . $sprint->name,
                $actor?->name . ' đã cập nhật sprint "' . $sprint->name . '" (' . $changedFieldsText . ').',
                $actor,
                ['sprint_name' => $sprint->name, 'changed_fields' => $changedFields],
                '/projects/' . $project->id . '/sprints',
            );
        }
    }
}
