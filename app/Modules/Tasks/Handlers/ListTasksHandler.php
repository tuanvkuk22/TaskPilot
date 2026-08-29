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
        $tasks = $this->repository->getFilteredTasks($projectId, $status, $priority);
        $allTasks = $this->repository->getFilteredTasks($projectId, null, null);

        $stats = [
            'total' => $allTasks->count(),
            'pending' => $allTasks->whereIn('status', ['todo', 'backlog', 'open', 'new'])->count(),
            'in_progress' => $allTasks->whereIn('status', ['in_progress', 'doing', 'review', 'in_review'])->count(),
            'done' => $allTasks->whereIn('status', ['done', 'completed', 'resolved', 'closed'])->count(),
            'high_priority' => $allTasks->whereIn('priority', ['high', 'highest', 'urgent'])->count(),
        ];

        return [
            'projects' => $this->repository->getAccessibleProjects($userId),
            'tasks' => $tasks,
            'stats' => $stats,
            'selectedProject' => $projectId,
            'selectedStatus' => $status,
            'selectedPriority' => $priority,
        ];
    }
}
