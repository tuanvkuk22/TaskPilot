<?php

namespace ProjectsManager\Modules\Projects\Handlers;

use ProjectsManager\Project;
use ProjectsManager\Modules\Projects\Repositories\ProjectRepository;

class UpdateProjectHandler
{
    /**
     * Create a new handler instance.
     *
     * @param ProjectRepository $repository
     */
    public function __construct(
        private readonly ProjectRepository $repository
    ) {
    }

    /**
     * Execute project update use case.
     *
     * @param Project $project
     * @param array $data
     *
     * @return bool
     */
    public function handle(Project $project, array $data): bool
    {
        return $this->repository->update($project, [
            'name' => $data['name'] ?? $project->name,
            'description' => $data['description'] ?? $project->description,
            'status' => $data['status'] ?? $project->status,
        ]);
    }
}
