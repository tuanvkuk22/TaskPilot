<?php

namespace ProjectsManager\Modules\Projects\Handlers;

use Illuminate\Database\Eloquent\Model;
use ProjectsManager\Project;
use ProjectsManager\ProjectRole;

class AssignMemberRoleHandler
{
    public function handle(Project $project, int $userId, string $roleName, array $permissions = []): bool
    {
        $role = $project->roles()->firstOrCreate(
            ['name' => $roleName],
            ['permissions' => []]
        );

        if (! empty($permissions)) {
            $role->permissions = array_values(array_unique($permissions));
            $role->save();
        }

        $permissionsToAttach = $role->permissions ?? [];

        $project->users()->updateExistingPivot($userId, [
            'role' => $roleName,
            'permissions' => json_encode($permissionsToAttach),
        ]);

        return true;
    }
}
