<?php

namespace ProjectsManager\Modules\Tasks\Handlers;

use ProjectsManager\Modules\Tasks\Repositories\TaskRepository;
use ProjectsManager\Task;

class UpdateTaskHandler
{
    /**
     * Create a new handler instance.
     *
     * @param TaskRepository $repository
     */
    public function __construct(
        private readonly TaskRepository $repository
    ) {
    }

    /**
     * Execute task update use case.
     *
     * @param Task $task
     * @param array $data
     *
     * @return bool
     */
    public function handle(Task $task, array $data): bool
    {
        return $this->repository->update($task, [
            'name' => $data['name'] ?? $task->name,
            'description' => $data['description'] ?? $task->description,
            'status' => $data['status'] ?? $task->status,
            'priority' => $data['priority'] ?? $task->priority,
            'project_id' => $data['project_id'] ?? $task->project_id,
            'days' => $data['days'] ?? $task->days,
            'hours' => $data['hours'] ?? $task->hours,
            'issue_type' => $data['issue_type'] ?? $task->issue_type,
            'assignee_id' => $data['assignee_id'] ?? $task->assignee_id,
            'due_date' => $data['due_date'] ?? $task->due_date,
            'story_points' => $data['story_points'] ?? $task->story_points,
            'labels' => $data['labels'] ?? $task->labels,
            'sprint' => $data['sprint'] ?? $task->sprint,
            'parent_id' => $data['parent_id'] ?? $task->parent_id,
            'sprint_id' => $data['sprint_id'] ?? $task->sprint_id,
        ]);
    }
}
