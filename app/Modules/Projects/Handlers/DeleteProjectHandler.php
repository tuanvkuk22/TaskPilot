<?php

namespace ProjectsManager\Modules\Projects\Handlers;

use ProjectsManager\Project;
use ProjectsManager\Modules\Projects\Repositories\ProjectRepository;

class DeleteProjectHandler
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
     * Execute project deletion use case.
     *
     * @param Project $project
     *
     * @return bool|null
     */
    public function handle(Project $project): ?bool
    {
        return $this->repository->delete($project);
    }
}
