<?php

namespace ProjectsManager\Policies;

use ProjectsManager\Task;
use ProjectsManager\User;

class TaskPolicy
{
    /**
     * Allow assignment by the owner or members with explicit task permissions.
     *
     * @param User $user
     * @param Task $task
     *
     * @return bool
     */
    public function assign(User $user, Task $task): bool
    {
        if (!$task->project_id) {
            return (int) $task->user_id === (int) $user->id;
        }

        $project = $task->project;
        if (!$project) {
            return false;
        }
        if ((int) $project->user_id === (int) $user->id) {
            return true;
        }

        $member = $project->users()->where('users.id', $user->id)->first();
        if (!$member) {
            return false;
        }

        $permissions = $member->pivot->permissions ?? [];
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true);
        }
        $role = $project->roles()->where('name', $member->pivot->role)->first();
        $permissions = array_merge(is_array($permissions) ? $permissions : [], $role->permissions ?? []);

        return (bool) array_intersect(['task.assign', 'task.manage'], $permissions);
    }
}
