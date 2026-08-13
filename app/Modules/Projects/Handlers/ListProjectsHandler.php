<?php

namespace ProjectsManager\Modules\Projects\Handlers;

use Illuminate\Database\Eloquent\Collection;
use ProjectsManager\Modules\Projects\Repositories\ProjectRepository;

class ListProjectsHandler
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
     * Execute project listing use case.
     *
     * @param int $userId
     * @param string|null $status
     *
     * @return Collection
     */
    public function handle(int $userId, ?string $status = null): Collection
    {
        return $this->repository->getProjectsForUser($userId, $status);
    }
}
