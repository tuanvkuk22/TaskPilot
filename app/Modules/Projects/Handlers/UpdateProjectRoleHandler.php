<?php

namespace ProjectsManager\Modules\Projects\Handlers;

use ProjectsManager\ProjectRole;

class UpdateProjectRoleHandler
{
    public function handle(ProjectRole $role, string $name, array $permissions = []): bool
    {
        return $role->update([
            'name' => trim($name),
            'permissions' => array_values(array_unique($permissions)),
        ]);
    }
}
