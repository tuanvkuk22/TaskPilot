<?php

namespace ProjectsManager\Modules\Teams\Handlers;

use Illuminate\Database\Eloquent\Collection;
use ProjectsManager\Modules\Teams\Repositories\TeamRepository;

class ListTeamsHandler
{
    /**
     * Create a new handler instance.
     *
     * @param TeamRepository $repository
     */
    public function __construct(
        private readonly TeamRepository $repository
    ) {
    }

    /**
     * Execute list teams use case.
     *
     * @param int $userId
     *
     * @return Collection
     */
    public function handle(int $userId): Collection
    {
        return $this->repository->getTeamsForUser($userId);
    }
}
