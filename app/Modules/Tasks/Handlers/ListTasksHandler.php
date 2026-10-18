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

        $accessibleProjects = $this->repository->getAccessibleProjects($userId);
        $selectedProj = $projectId ? $accessibleProjects->firstWhere('id', (int) $projectId) : null;
        $effectiveWorkflow = $selectedProj ? $selectedProj->effective_workflow : \ProjectsManager\Project::defaultWorkflowForType('software');

        $projectWorkflows = [];
        foreach ($accessibleProjects as $p) {
            $projectWorkflows[$p->id] = $p->effective_workflow;
        }

        $stats = [
            'total' => $allTasks->count(),
            'pending' => $allTasks->filter(function ($t) {
                return in_array($t->status, ['todo', 'backlog', 'open', 'new', 'draft', 'application'], true);
            })->count(),
            'in_progress' => $allTasks->filter(function ($t) {
                return in_array($t->status, ['in_progress', 'doing', 'review', 'in_review', 'code_review', 'qa_testing', 'screening', 'interview', 'content_creation', 'design_review', 'approval', 'offer'], true);
            })->count(),
            'done' => $allTasks->filter(function ($t) {
                return in_array($t->status, ['done', 'completed', 'resolved', 'closed', 'published', 'onboarding'], true);
            })->count(),
            'high_priority' => $allTasks->whereIn('priority', ['high', 'highest', 'urgent', 'critical'])->count(),
        ];

        return [
            'projects' => $accessibleProjects,
            'tasks' => $tasks,
            'stats' => $stats,
            'selectedProject' => $projectId,
            'selectedStatus' => $status,
            'selectedPriority' => $priority,
            'effective_workflow' => $effectiveWorkflow,
            'project_workflows' => $projectWorkflows,
        ];
    }
}
