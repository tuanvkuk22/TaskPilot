<?php

namespace ProjectsManager\Observers;

use ProjectsManager\Project;
use ProjectsManager\Services\ActivityLogger;
use ProjectsManager\Services\NotificationService;
use ProjectsManager\User;

class ProjectObserver
{
    public function created(Project $project): void
    {
        ActivityLogger::record(
            projectId: $project->getKey(),
            action: ActivityLogger::ACTION_CREATED,
            userId: $project->user_id,
            subject: $project,
            meta: 'project',
        );

        $actor = User::find($project->user_id);

        $project->load('users');

        if ($project->users->isNotEmpty()) {
            NotificationService::sendToProject(
                $project,
                NotificationService::EVENT_PROJECT_CREATED,
                'Dự án mới: ' . $project->name,
                $actor?->name . ' đã tạo dự án "' . $project->name . '".',
                $actor,
                ['project_code' => $project->project_code],
                '/projects/' . $project->id,
            );
        }
    }

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

        $actor = User::find(auth()->id());

        $project->load('users');

        $changedFields = array_keys($changes);
        $changedFieldsText = implode(', ', $changedFields);

        if ($project->users->isNotEmpty()) {
            NotificationService::sendToProject(
                $project,
                NotificationService::EVENT_PROJECT_UPDATED,
                'Dự án được cập nhật: ' . $project->name,
                $actor?->name . ' đã cập nhật thông tin dự án "' . $project->name . '" (' . $changedFieldsText . ').',
                $actor,
                ['changed_fields' => $changedFields],
                '/projects/' . $project->id,
            );
        }
    }

    public function archived(Project $project): void
    {
        ActivityLogger::record(
            projectId: $project->getKey(),
            action: ActivityLogger::ACTION_ARCHIVED,
            userId: auth()->id(),
            subject: $project,
        );

        $actor = User::find(auth()->id());

        $project->load('users');

        if ($project->users->isNotEmpty()) {
            NotificationService::sendToProject(
                $project,
                NotificationService::EVENT_PROJECT_ARCHIVED,
                'Dự án được lưu trữ: ' . $project->name,
                $actor?->name . ' đã lưu trữ dự án "' . $project->name . '".',
                $actor,
                ['project_code' => $project->project_code],
                '/projects/' . $project->id,
            );
        }
    }

    public function deleted(Project $project): void
    {
        try {
            $project->load('users');

            if ($project->users->isNotEmpty()) {
                $actor = User::find(auth()->id());

                NotificationService::sendToProject(
                    $project,
                    NotificationService::EVENT_PROJECT_DELETED,
                    'Dự án bị xóa: ' . $project->name,
                    $actor?->name . ' đã xóa dự án "' . $project->name . '".',
                    $actor,
                    ['project_code' => $project->project_code],
                    null,
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::info('ProjectObserver::deleted skipped: ' . $e->getMessage());
        }
    }
}
