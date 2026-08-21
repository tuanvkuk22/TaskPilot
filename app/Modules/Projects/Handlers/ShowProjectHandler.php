<?php

namespace ProjectsManager\Modules\Projects\Handlers;

use ProjectsManager\Project;

class ShowProjectHandler
{
    /**
     * Execute show project use case.
     *
     * @param Project $project
     *
     * @return Project
     */
    public function handle(Project $project): Project
    {
        $project->load(['users', 'permissions', 'comments', 'tasks', 'category', 'team', 'roles']);

        return $project;
    }
}
