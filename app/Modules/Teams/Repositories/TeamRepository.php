<?php

namespace ProjectsManager\Modules\Teams\Repositories;

use Illuminate\Database\Eloquent\Collection;
use ProjectsManager\Team;
use ProjectsManager\User;

class TeamRepository
{
    /**
     * Get teams accessible by user.
     *
     * @param int $userId
     *
     * @return Collection
     */
    public function getTeamsForUser(int $userId): Collection
    {
        return Team::query()
            ->withCount('users')
            ->where('user_id', $userId)
            ->orWhereHas('users', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->latest()
            ->get();
    }

    /**
     * Create a new team.
     *
     * @param array $data
     *
     * @return Team
     */
    public function create(array $data): Team
    {
        return Team::create($data);
    }

    /**
     * Attach user to team with role and permissions.
     *
     * @param Team $team
     * @param int $userId
     * @param string $role
     * @param array $permissions
     *
     * @return void
     */
    public function attachUser(Team $team, int $userId, string $role, array $permissions): void
    {
        $team->users()->attach($userId, [
            'role' => $role,
            'permissions' => json_encode($permissions),
        ]);
    }

    /**
     * Check if team has user attached.
     *
     * @param Team $team
     * @param int $userId
     *
     * @return bool
     */
    public function hasUser(Team $team, int $userId): bool
    {
        return $team->users()->where('users.id', $userId)->exists();
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
        $identifier = trim($email);

        return User::whereRaw('LOWER(email) = ?', [strtolower($identifier)])
            ->orWhereRaw('LOWER(username) = ?', [strtolower($identifier)])
            ->first();
    }
}
