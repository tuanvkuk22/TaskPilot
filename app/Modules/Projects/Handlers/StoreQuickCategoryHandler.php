<?php

namespace ProjectsManager\Modules\Projects\Handlers;

use ProjectsManager\Modules\Projects\Repositories\ProjectRepository;
use ProjectsManager\ProjectCategory;

class StoreQuickCategoryHandler
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
     * Execute quick category creation use case.
     *
     * @param int $userId
     * @param string $name
     *
     * @return ProjectCategory
     */
    public function handle(int $userId, string $name): ProjectCategory
    {
        return $this->repository->firstOrCreateCategory($userId, $name);
    }
}
