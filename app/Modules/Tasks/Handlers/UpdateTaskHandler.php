<?php

namespace ProjectsManager\Modules\Tasks\Handlers;

use Illuminate\Support\Facades\Log;
use ProjectsManager\Modules\Tasks\Repositories\TaskRepository;
use ProjectsManager\Task;
use ProjectsManager\Events\ProjectTaskUpdated;

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
        $update = [];
        $fields = [
            'name', 'description', 'status', 'priority', 'project_id',
            'days', 'hours', 'issue_type', 'assignee_id', 'reporter_id',
            'start_date', 'end_date', 'due_date', 'story_points', 'labels',
            'sprint', 'parent_id', 'sprint_id'
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = $data[$field] === '' ? null : $data[$field];
            }
        }

        return $this->repository->update($task, $update);
    }
}
