<?php

namespace ProjectsManager\Observers;

use ProjectsManager\Comment;
use ProjectsManager\Services\ActivityLogger;

class CommentObserver
{
    public function created(Comment $comment): void
    {
        $projectId = $this->resolveProjectId($comment);
        if (! $projectId) {
            return;
        }

        ActivityLogger::record(
            projectId: $projectId,
            action: ActivityLogger::ACTION_COMMENTED,
            userId: $comment->user_id,
            subject: $comment,
            meta: mb_substr((string) $comment->body, 0, 120),
        );
    }

    public function updated(Comment $comment): void
    {
        $projectId = $this->resolveProjectId($comment);
        if (! $projectId) {
            return;
        }

        $changes = $comment->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        ActivityLogger::record(
            projectId: $projectId,
            action: ActivityLogger::ACTION_COMMENT_EDITED,
            userId: auth()->id(),
            subject: $comment,
            changes: array_intersect_key($changes, array_flip(['body'])),
        );
    }

    public function deleted(Comment $comment): void
    {
        $projectId = $this->resolveProjectId($comment);
        if (! $projectId) {
            return;
        }

        ActivityLogger::record(
            projectId: $projectId,
            action: ActivityLogger::ACTION_COMMENT_DELETED,
            userId: auth()->id(),
            subjectLabel: 'Comment',
        );
    }

    private function resolveProjectId(Comment $comment): ?int
    {
        if ($comment->commentable_type === \ProjectsManager\Task::class) {
            $task = \ProjectsManager\Task::find($comment->commentable_id);
            return $task?->project_id;
        }

        if ($comment->commentable_type === \ProjectsManager\Project::class) {
            return (int) $comment->commentable_id;
        }

        return null;
    }
}