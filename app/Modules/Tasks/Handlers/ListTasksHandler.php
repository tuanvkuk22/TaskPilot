<?php

namespace ProjectsManager\Modules\Tasks\Handlers;

use ProjectsManager\Modules\Tasks\Repositories\TaskRepository;

class ListTasksHandler
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
     * Execute list tasks use case.
     *
     * @param int $userId
     * @param mixed $projectId
     * @param string|null $status
     * @param string|null $priority
     *
     * @return array
     */
    public function handle(int $userId, mixed $projectId = null, ?string $status = null, ?string $priority = null): array
    {
        return [
            'projects' => $this->repository->getAccessibleProjects($userId),
            'tasks' => $this->repository->getFilteredTasks($projectId, $status, $priority),
            'selectedProject' => $projectId,
            'selectedStatus' => $status,
            'selectedPriority' => $priority,
        ];
    }
}
