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
        $projectIds = Project::query()
            ->where('user_id', $user->id)
            ->orWhereHas('users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->pluck('id');

        $unassignedTasks = Task::query()
            ->with('project')
            ->whereIn('project_id', $projectIds)
            ->whereNull('assignee_id')
            ->whereNotIn('status', ['done', 'closed', 'completed', 'resolved'])
            ->latest()
            ->limit(3)
            ->get();

        return [
            'totalProjects' => Project::count(),
            'totalTasks' => Task::count(),
            'totalCompanies' => Company::count(),
            'userTasks' => $user->assignedTasks()->count(),
            'unassignedTasks' => $unassignedTasks,
        ];
    }
}
