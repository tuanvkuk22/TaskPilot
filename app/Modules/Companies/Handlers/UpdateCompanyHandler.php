<?php

namespace ProjectsManager\Modules\Companies\Handlers;

use ProjectsManager\Company;
use ProjectsManager\Modules\Companies\Repositories\CompanyRepository;

class UpdateCompanyHandler
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
     * Execute company update use case.
     *
     * @param Company $company
     * @param array $data
     *
     * @return bool
     */
    public function handle(Company $company, array $data): bool
    {
        return $this->repository->update($company, [
            'name' => $data['name'] ?? $company->name,
            'description' => $data['description'] ?? $company->description,
            'contact_phone' => $data['contact_phone'] ?? $company->contact_phone,
            'contact_email' => $data['contact_email'] ?? $company->contact_email,
            'contact_address' => $data['contact_address'] ?? $company->contact_address,
            'contact_note' => $data['contact_note'] ?? $company->contact_note,
        ]);
    }
}
