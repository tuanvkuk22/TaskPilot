<?php

namespace ProjectsManager\Modules\Tasks\Handlers;

use ProjectsManager\Modules\Tasks\Repositories\TaskRepository;

class CreateTaskHandler
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
     * Execute create task form data retrieval use case.
     *
     * @param int $userId
     * @param mixed $projectId
     *
     * @return array
     */
    public function handle(int $userId, mixed $projectId = null): array
    {
        return [
            'projects' => $this->repository->getAccessibleProjects($userId),
            'project_id' => $projectId,
            'users' => $this->repository->getAssignableUsers($userId, $projectId),
            'reporters' => $this->repository->getAssignableUsers($userId, $projectId),
            'epics' => $this->repository->getEpics($projectId),
            'sprints' => $this->repository->getSprints($projectId),
            'available_labels' => $this->repository->getAllLabels(),
        ];
    }
}
