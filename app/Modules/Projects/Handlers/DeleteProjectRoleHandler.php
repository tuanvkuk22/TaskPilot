<?php

namespace ProjectsManager\Modules\Projects\Handlers;

use ProjectsManager\ProjectRole;

class DeleteProjectRoleHandler
{
    public function handle(ProjectRole $role): ?bool
    {
        return $role->delete();
    }
}
