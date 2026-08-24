<?php

namespace ProjectsManager\Modules\Projects\Handlers;

use Illuminate\Database\Eloquent\Collection;
use ProjectsManager\ProjectRole;

class ListProjectRolesHandler
{
    public function handle(int $projectId): Collection
    {
        return ProjectRole::where('project_id', $projectId)->orderBy('name')->get();
    }
}
