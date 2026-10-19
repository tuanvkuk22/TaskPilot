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
        $projects = $this->repository->getAccessibleProjects($userId);
        $selectedProject = $projectId ? $projects->firstWhere('id', (int) $projectId) : $projects->first();
        $effectiveWorkflow = $selectedProject ? $selectedProject->effective_workflow : \ProjectsManager\Project::defaultWorkflowForType('software');

        return [
            'projects' => $projects,
            'project_id' => $projectId,
            'selected_project' => $selectedProject,
            'effective_workflow' => $effectiveWorkflow,
            'users' => $this->repository->getAssignableUsers($userId, $projectId),
            'reporters' => $this->repository->getAssignableUsers($userId, $projectId),
            'epics' => $this->repository->getEpics($projectId),
            'sprints' => $this->repository->getSprints($projectId),
            'available_labels' => $this->repository->getAllLabels(),
        ];
    }
}
