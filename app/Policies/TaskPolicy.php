<?php

namespace ProjectsManager\Policies;

use ProjectsManager\Project;
use ProjectsManager\Task;
use ProjectsManager\User;

class TaskPolicy
{
    /**
     * Allow viewing AI suggestions for a task the user can access.
     */
    public function recommend(User $user, Task $task): bool
    {
        return $this->canAccessTask($user, $task);
    }

    /**
     * Allow assignment by the owner, admins, or members who can edit/assign tasks.
     */
    public function assign(User $user, Task $task): bool
    {
        return $this->hasTaskPermission($user, $task, ['task.assign', 'task.manage', 'task.edit']);
    }

    protected function canAccessTask(User $user, Task $task): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }
        if (!$task->project_id) {
            return (int) $task->user_id === (int) $user->id;
        }

        $project = $task->project;
        if (!$project) {
            return (int) $task->user_id === (int) $user->id;
        }
        if ($this->isProjectOwner($user, $project)) {
            return true;
        }

        return $project->users()->where('users.id', $user->id)->exists();
    }

    protected function hasTaskPermission(User $user, Task $task, array $needed): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }
        if (!$task->project_id) {
            return (int) $task->user_id === (int) $user->id;
        }

        $project = $task->project;
        if (!$project) {
            return false;
        }
        if ($this->isProjectOwner($user, $project)) {
            return true;
        }

        $member = $project->users()->where('users.id', $user->id)->first();
        if (!$member) {
            return false;
        }

        $permissions = $this->memberPermissions($project, $member);

        return (bool) array_intersect($needed, $permissions);
    }

    protected function isProjectOwner(User $user, Project $project): bool
    {
        return (int) $project->user_id === (int) $user->id;
    }

    protected function isAdmin(User $user): bool
    {
        if ((int) $user->role_id === 1) {
            return true;
        }
        if (!$user->role_id) {
            return false;
        }
        $name = strtolower((string) ($user->role->name ?? ''));

        return in_array($name, ['admin', 'administrator'], true);
    }

    protected function memberPermissions(Project $project, User $member): array
    {
        $permissions = $member->pivot->permissions ?? [];
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?: [];
        }
        if (!is_array($permissions)) {
            $permissions = [];
        }

        $role = $this->matchingProjectRole($project, (string) ($member->pivot->role ?? ''));
        $rolePermissions = $role->permissions ?? [];
        if (is_string($rolePermissions)) {
            $rolePermissions = json_decode($rolePermissions, true) ?: [];
        }
        if (is_array($rolePermissions)) {
            $permissions = array_merge($permissions, $rolePermissions);
        }

        if ($permissions === []) {
            $permissions = $this->impliedPermissionsForRole((string) ($member->pivot->role ?? ''));
        }

        return $permissions;
    }

    protected function matchingProjectRole(Project $project, string $roleName)
    {
        if ($roleName === '') {
            return null;
        }

        $aliases = $this->roleAliases($roleName);

        return $project->roles()
            ->where(function ($query) use ($roleName, $aliases) {
                $query->where('name', $roleName)->orWhereIn('name', $aliases);
            })
            ->first();
    }

    protected function roleAliases(string $roleName): array
    {
        return match (mb_strtolower(trim($roleName))) {
            'owner', 'admin', 'manager', 'quản lý' => ['Quản lý', 'owner', 'manager'],
            'member', 'thành viên' => ['Thành viên', 'member'],
            'viewer', 'người xem' => ['Người xem', 'viewer'],
            default => [$roleName],
        };
    }

    /**
     * Dataset import stores English role names without a permissions JSON.
     */
    protected function impliedPermissionsForRole(string $roleName): array
    {
        return match (mb_strtolower(trim($roleName))) {
            'owner', 'admin', 'manager', 'quản lý' => ['task.manage', 'task.assign', 'task.edit', 'task.view'],
            'member', 'thành viên' => ['task.edit', 'task.view', 'task.create'],
            default => [],
        };
    }
}
