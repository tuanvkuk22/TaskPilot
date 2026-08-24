<?php

namespace ProjectsManager\Modules\Projects\Handlers;

use ProjectsManager\ProjectRole;

class CreateProjectRoleHandler
{
    public function handle(int $projectId, string $name, array $permissions = []): ProjectRole
    {
        return ProjectRole::create([
            'project_id' => $projectId,
            'name' => trim($name),
            'permissions' => array_values(array_unique($permissions)),
        ]);
    }
}
