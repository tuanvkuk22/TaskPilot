<?php

namespace ProjectsManager\Modules\Companies\Handlers;

use ProjectsManager\Company;
use ProjectsManager\Modules\Companies\Repositories\CompanyRepository;

class DeleteCompanyHandler
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
     * Execute company deletion use case.
     *
     * @param Company $company
     *
     * @return bool|null
     */
    public function handle(Company $company): ?bool
    {
        return $this->repository->delete($company);
    }
}
