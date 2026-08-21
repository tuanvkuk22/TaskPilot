<?php

namespace ProjectsManager\Modules\Projects\Repositories;

use Illuminate\Database\Eloquent\Collection;
use ProjectsManager\Company;
use ProjectsManager\Project;
use ProjectsManager\ProjectCategory;
use ProjectsManager\Team;
use ProjectsManager\User;

class ProjectRepository
{
    /**
     * Get projects accessible by user with status filter.
     *
     * @param int $userId
     * @param string|null $status
     *
     * @return Collection
     */
    public function getProjectsForUser(int $userId, ?string $status = null): Collection
    {
        return Project::query()
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('users', function ($userQuery) use ($userId) {
                        $userQuery->where('users.id', $userId);
                    });
            })
            ->statusFilter($status)
            ->orderBy('updated_at', 'desc')
            ->with('tasks')
            ->get();
    }

    /**
     * Find project by ID.
     *
     * @param int $id
     *
     * @return Project|null
     */
    public function findById(int $id): ?Project
    {
        return Project::find($id);
    }

    /**
     * Create a new project.
     *
     * @param array $data
     *
     * @return Project
     */
    public function create(array $data): Project
    {
        return Project::create($data);
    }

    /**
     * Update project attributes.
     *
     * @param Project $project
     * @param array $data
     *
     * @return bool
     */
    public function update(Project $project, array $data): bool
    {
        return $project->update($data);
    }

    /**
     * Delete a project.
     *
     * @param Project $project
     *
     * @return bool|null
     */
    public function delete(Project $project): ?bool
    {
        return $project->delete();
    }

    /**
     * Archive project.
     *
     * @param Project $project
     *
     * @return bool
     */
    public function archive(Project $project): bool
    {
        return $project->update([
            'status' => 'archived',
            'archived' => true,
        ]);
    }

    /**
     * Get project categories owned by user.
     *
     * @param int $userId
     *
     * @return Collection
     */
    public function getCategoriesForUser(int $userId): Collection
    {
        return ProjectCategory::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Find or create project category by name.
     *
     * @param int $userId
     * @param string $name
     *
     * @return ProjectCategory
     */
    public function firstOrCreateCategory(int $userId, string $name): ProjectCategory
    {
        return ProjectCategory::firstOrCreate([
            'user_id' => $userId,
            'name' => trim($name),
        ]);
    }

    /**
     * Get user teams for project creation form.
     *
     * @param int $userId
     *
     * @return Collection
     */
    public function getTeamsForUser(int $userId): Collection
    {
        return Team::query()
            ->where('user_id', $userId)
            ->orWhereHas('users', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Get user companies for project form.
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
     * Find user by lowercased email.
     *
     * @param string $email
     *
     * @return User|null
     */
    public function findUserByEmail(string $email): ?User
    {
        return User::whereRaw('LOWER(email) = ?', [strtolower(trim($email))])->first();
    }

    /**
     * Attach user to project with role and permissions.
     *
     * @param Project $project
     * @param int $userId
     * @param string $role
     * @param array $permissions
     *
     * @return void
     */
    public function attachUser(Project $project, int $userId, string $role, array $permissions): void
    {
        $project->users()->attach($userId, [
            'role' => $role,
            'permissions' => json_encode($permissions),
        ]);
    }

    /**
     * Check if user is already attached to project.
     *
     * @param Project $project
     * @param int $userId
     *
     * @return bool
     */
    public function hasUser(Project $project, int $userId): bool
    {
        return $project->users()->where('users.id', $userId)->exists();
    }

    /**
     * Check if user is assigned to owner projects or teams.
     *
     * @param int $userId
     * @param int $ownerId
     *
     * @return bool
     */
    public function isUserAssignedToOwnerProjectsOrTeams(int $userId, int $ownerId): bool
    {
        $inProject = Project::where('user_id', $ownerId)
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->exists();

        if ($inProject) {
            return true;
        }

        return Team::where('user_id', $ownerId)
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->exists();
    }

    /**
     * Find team by ID.
     *
     * @param int $teamId
     *
     * @return Team|null
     */
    public function findTeamById(int $teamId): ?Team
    {
        return Team::find($teamId);
    }

    /**
     * Generate unique project code.
     *
     * @param Project $project
     *
     * @return string
     */
    public function generateProjectCode(Project $project): string
    {
        $prefix = 'PRJ';
        $datePart = now()->format('Ymd');
        $maxId = (int) Project::max('id') + 1;
        $suffix = str_pad((string) $maxId, 4, '0', STR_PAD_LEFT);
        $code = $prefix . '-' . $datePart . '-' . $suffix;

        while (Project::where('project_code', $code)->whereKeyNot($project->getKey())->exists()) {
            $maxId++;
            $suffix = str_pad((string) $maxId, 4, '0', STR_PAD_LEFT);
            $code = $prefix . '-' . $datePart . '-' . $suffix;
        }

        return $code;
    }

    /**
     * Return the next project code for the create form preview.
     *
     * @return string
     */
    public function previewProjectCode(): string
    {
        $datePart = now()->format('Ymd');
        $suffix = str_pad((string) ((int) Project::max('id') + 1), 4, '0', STR_PAD_LEFT);

        return 'PRJ-' . $datePart . '-' . $suffix;
    }

    public function countForUser(int $userId, ?string $status = null): int
    {
        $query = Project::query()
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('users', function ($userQuery) use ($userId) {
                        $userQuery->where('users.id', $userId);
                    });
            });

        if ($status) {
            $query->where('status', $status);
        }

        return $query->count();
    }
}
