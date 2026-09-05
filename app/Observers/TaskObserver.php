<?php

namespace ProjectsManager\Observers;

use ProjectsManager\Services\ActivityLogger;
use ProjectsManager\Services\NotificationService;
use ProjectsManager\Task;
use ProjectsManager\User;

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

        $project = $task->project;
        $actor = User::find($task->user_id);

        if ($project) {
            NotificationService::sendToProject(
                $project,
                NotificationService::EVENT_TASK_CREATED,
                'Công việc mới: ' . $task->name,
                $actor?->name . ' đã tạo công việc ' . ($task->issue_key ?? '') . ' "' . $task->name . '".',
                $actor,
                ['issue_type' => $task->issue_type, 'issue_key' => $task->issue_key],
                '/tasks/' . $task->id,
            );
        }

        if ($task->assignee_id && $task->assignee_id !== $task->user_id) {
            $task->load('assignee');
            NotificationService::sendToAssignee(
                $task,
                NotificationService::EVENT_TASK_ASSIGNED,
                'Công việc được giao: ' . $task->name,
                'Bạn đã được giao công việc ' . ($task->issue_key ?? '') . ' "' . $task->name . '" trên dự án ' . ($project?->name ?? '').'.',
                $actor,
                ['issue_key' => $task->issue_key, 'priority' => $task->priority],
                $task->assignee_id ? '/tasks/' . $task->id : null,
            );
        }
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

        $project = $task->project;
        $actor = User::find(auth()->id());

        if ($project) {
            $message = $actor?->name . ' đã cập nhật công việc ' . ($task->issue_key ?? '') . ' "' . $task->name . '".';

            if (array_key_exists('assignee_id', $humanChanges) && $task->assignee_id) {
                $task->load('assignee');
                NotificationService::sendToAssignee(
                    $task,
                    NotificationService::EVENT_TASK_ASSIGNED,
                    'Công việc được giao lại: ' . $task->name,
                    'Công việc ' . ($task->issue_key ?? '') . ' "' . $task->name . '" đã được giao cho bạn.',
                    $actor,
                    ['issue_key' => $task->issue_key],
                    '/tasks/' . $task->id,
                );
            }

            if (array_key_exists('status', $humanChanges)) {
                $message = $actor?->name . ' đã thay đổi trạng thái công việc ' . ($task->issue_key ?? '') . ' "' . $task->name . '" sang "' . self::statusLabel($humanChanges['status']['new'] ?? $task->status) . '".';
            }

            NotificationService::sendToProject(
                $project,
                NotificationService::EVENT_TASK_UPDATED,
                'Công việc được cập nhật: ' . $task->name,
                $message,
                $actor,
                ['issue_key' => $task->issue_key, 'changes' => $humanChanges],
                '/tasks/' . $task->id,
            );
        }
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

        $project = $task->project;
        $actor = User::find(auth()->id());

        if ($project) {
            NotificationService::sendToProject(
                $project,
                NotificationService::EVENT_TASK_DELETED,
                'Công việc bị xóa: ' . $task->name,
                $actor?->name . ' đã xóa công việc ' . ($task->issue_key ?? '') . ' "' . $task->name . '".',
                $actor,
                ['issue_key' => $task->issue_key],
                '/projects/' . $task->project_id,
            );
        }
    }

    private static function statusLabel(string $status): string
    {
        return match ($status) {
            'todo' => 'Chưa làm',
            'in_progress' => 'Đang làm',
            'done' => 'Hoàn thành',
            default => $status,
        };
    }
}
