<?php

namespace ProjectsManager\Modules\Companies\Handlers;

use Illuminate\Database\Eloquent\Collection;
use ProjectsManager\Modules\Companies\Repositories\CompanyRepository;

class ListCompaniesHandler
{
    /**
     * Create a new handler instance.
     *
     * @param CompanyRepository $repository
     */
    public function __construct(
        private readonly CompanyRepository $repository
    ) {
    }

    /**
     * Execute list companies use case.
     *
     * @param int $userId
     *
     * @return Collection
     */
    public function handle(int $userId): Collection
    {
        return $this->repository->getCompaniesForUser($userId);
    }
}
