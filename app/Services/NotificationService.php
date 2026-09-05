<?php

namespace ProjectsManager\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use ProjectsManager\Notifications\TaskPilotNotification;
use ProjectsManager\Project;
use ProjectsManager\Task;
use ProjectsManager\User;

class NotificationService
{
    public const EVENT_TASK_CREATED = 'task.created';
    public const EVENT_TASK_UPDATED = 'task.updated';
    public const EVENT_TASK_DELETED = 'task.deleted';
    public const EVENT_TASK_ASSIGNED = 'task.assigned';
    public const EVENT_TASK_STATUS_CHANGED = 'task.status_changed';
    public const EVENT_COMMENT_CREATED = 'comment.created';
    public const EVENT_COMMENT_UPDATED = 'comment.updated';
    public const EVENT_COMMENT_DELETED = 'comment.deleted';
    public const EVENT_PROJECT_CREATED = 'project.created';
    public const EVENT_PROJECT_UPDATED = 'project.updated';
    public const EVENT_PROJECT_ARCHIVED = 'project.archived';
    public const EVENT_PROJECT_DELETED = 'project.deleted';
    public const EVENT_PROJECT_MEMBER_ADDED = 'project.member_added';
    public const EVENT_SPRINT_CREATED = 'sprint.created';
    public const EVENT_SPRINT_UPDATED = 'sprint.updated';
    public const EVENT_WORKLOG_CREATED = 'worklog.created';
    public const EVENT_TEAM_INVITATION = 'team.invitation';

    public static function sendToProject(
        Project $project,
        string $eventType,
        string $title,
        string $message,
        ?User $actor = null,
        ?array $data = null,
        ?string $url = null,
    ): void
    {
        $recipients = $project->relationLoaded('users')
            ? $project->users
            : $project->users()->get();

        $recipients = static::prepareRecipients($recipients, $actor);

        if ($recipients->isEmpty()) {
            return;
        }

        $notification = new TaskPilotNotification($eventType, $title, $message, $data, $url, $actor);

        static::safeSend($recipients, $notification);
    }

    public static function sendToAssignee(
        Task $task,
        string $eventType,
        string $title,
        string $message,
        ?User $actor = null,
        ?array $data = null,
        ?string $url = null,
    ): void
    {
        if (! $task->assignee) {
            return;
        }

        if ($actor && $task->assignee->id === $actor->id) {
            return;
        }

        $notification = new TaskPilotNotification($eventType, $title, $message, $data, $url, $actor);

        static::safeSend($task->assignee, $notification);
    }

    public static function sendToTaskCreator(
        Task $task,
        string $eventType,
        string $title,
        string $message,
        ?User $actor = null,
        ?array $data = null,
        ?string $url = null,
    ): void
    {
        if (! $task->user) {
            return;
        }

        if ($actor && $task->user->id === $actor->id) {
            return;
        }

        $notification = new TaskPilotNotification($eventType, $title, $message, $data, $url, $actor);

        static::safeSend($task->user, $notification);
    }

    public static function sendToUser(
        User $user,
        string $eventType,
        string $title,
        string $message,
        ?User $actor = null,
        ?array $data = null,
        ?string $url = null,
    ): void
    {
        $notification = new TaskPilotNotification($eventType, $title, $message, $data, $url, $actor);

        static::safeSend($user, $notification);
    }

    public static function sendToMany(
        Collection $users,
        string $eventType,
        string $title,
        string $message,
        ?User $actor = null,
        ?array $data = null,
        ?string $url = null,
    ): void
    {
        $users = static::prepareRecipients($users, $actor);

        if ($users->isEmpty()) {
            return;
        }

        $notification = new TaskPilotNotification($eventType, $title, $message, $data, $url, $actor);

        static::safeSend($users, $notification);
    }

    protected static function prepareRecipients(Collection $recipients, ?User $actor): Collection
    {
        if ($actor) {
            $recipients = $recipients->reject(fn (User $u) => $u->id === $actor->id);
        }

        return $recipients;
    }

    protected static function safeSend($recipients, TaskPilotNotification $notification): void
    {
        try {
            Notification::send($recipients, $notification);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Notification dispatch failed: ' . $e->getMessage());
        }
    }
}
