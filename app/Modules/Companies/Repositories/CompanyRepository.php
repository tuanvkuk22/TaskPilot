<?php

namespace ProjectsManager\Modules\Companies\Repositories;

use Illuminate\Database\Eloquent\Collection;
use ProjectsManager\Company;

class CompanyRepository
{
    /**
     * Get companies owned by user.
     *
     * @param int $userId
     *
     * @return Collection
     */
    public function getCompaniesForUser(int $userId): Collection
    {
        return Company::where('user_id', $userId)->get();
    }

    /**
     * Find company by ID.
     *
     * @param int $id
     *
     * @return Company|null
     */
    public function findById(int $id): ?Company
    {
        return Company::find($id);
    }

    /**
     * Create a new company.
     *
     * @param array $data
     *
     * @return Company
     */
    public function create(array $data): Company
    {
        return Company::create($data);
    }

    /**
     * Update company details.
     *
     * @param Company $company
     * @param array $data
     *
     * @return bool
     */
    public function update(Company $company, array $data): bool
    {
        return $company->update($data);
    }

    /**
     * Delete company.
     *
     * @param Company $company
     *
     * @return bool|null
     */
    public function delete(Company $company): ?bool
    {
        return $company->delete();
    }
}
