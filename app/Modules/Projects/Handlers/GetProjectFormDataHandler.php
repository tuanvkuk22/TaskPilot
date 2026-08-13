<?php

namespace ProjectsManager\Modules\Projects\Handlers;

use ProjectsManager\Modules\Projects\Repositories\ProjectRepository;

class GetProjectFormDataHandler
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
     * Execute project form data retrieval use case.
     *
     * @param int $userId
     * @param mixed $companyId
     *
     * @return array
     */
    public function handle(int $userId, mixed $companyId = null): array
    {
        return [
            'company_id' => $companyId,
            'companies' => $this->repository->getCompaniesForUser($userId),
            'categories' => $this->repository->getCategoriesForUser($userId),
            'teams' => $this->repository->getTeamsForUser($userId),
            'project_code_preview' => $this->repository->previewProjectCode(),
        ];
    }
}
