<?php

namespace ProjectsManager\Modules\Projects\Handlers;

use ProjectsManager\Project;
use ProjectsManager\Modules\Projects\Repositories\ProjectRepository;

class ArchiveProjectHandler
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
     * Execute project archive use case.
     *
     * @param Project $project
     *
     * @return bool
     */
    public function handle(Project $project): bool
    {
        return $this->repository->archive($project);
    }
}
