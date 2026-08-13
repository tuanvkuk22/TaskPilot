<?php

namespace ProjectsManager\Modules\Home\Handlers;

use ProjectsManager\Company;
use ProjectsManager\Project;
use ProjectsManager\Task;
use ProjectsManager\User;

class GetHomeDashboardHandler
{
    /**
     * Execute home dashboard metrics use case.
     *
     * @param User $user
     *
     * @return array
     */
    public function handle(User $user): array
    {
        return [
            'totalProjects' => Project::count(),
            'totalTasks' => Task::count(),
            'totalCompanies' => Company::count(),
            'userTasks' => $user->tasks()->count(),
        ];
    }
}
