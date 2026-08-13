<?php

namespace ProjectsManager\Modules\Companies\Handlers;

use ProjectsManager\Company;
use ProjectsManager\Modules\Companies\Repositories\CompanyRepository;

class StoreCompanyHandler
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
     * Execute company creation use case.
     *
     * @param int $userId
     * @param array $data
     *
     * @return Company
     */
    public function handle(int $userId, array $data): Company
    {
        return $this->repository->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'contact_address' => $data['contact_address'] ?? null,
            'contact_note' => $data['contact_note'] ?? null,
            'user_id' => $userId,
        ]);
    }
}
