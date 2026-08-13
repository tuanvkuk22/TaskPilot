<?php

namespace ProjectsManager\Modules\Companies\Handlers;

use ProjectsManager\Company;
use ProjectsManager\Modules\Companies\Repositories\CompanyRepository;

class ShowCompanyHandler
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
     * Execute show company details use case.
     *
     * @param Company $company
     *
     * @return Company
     */
    public function handle(Company $company): Company
    {
        return $this->repository->findById($company->id) ?? $company;
    }
}
