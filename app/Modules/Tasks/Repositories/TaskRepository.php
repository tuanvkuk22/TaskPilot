<?php

namespace ProjectsManager\Modules\Tasks\Repositories;

use Illuminate\Database\Eloquent\Collection;
use ProjectsManager\Project;
use ProjectsManager\Task;
use ProjectsManager\User;

class TaskRepository
{
    /**
     * Get tasks for filtering by project, status, priority.
     *
     * @param mixed $projectId
     * @param string|null $status
     * @param string|null $priority
     *
     * @return Collection
     */
    public function getFilteredTasks(mixed $projectId = null, ?string $status = null, ?string $priority = null): Collection
    {
        return Task::query()
            ->with('project', 'user', 'assignee', 'reporter')
            ->when($projectId, fn ($query) => $query->where('project_id', $projectId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($priority, fn ($query) => $query->where('priority', $priority))
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Get accessible user projects for tasks.
     *
     * @param int $userId
     *
     * @return Collection
     */
    public function getAccessibleProjects(int $userId): Collection
    {
        return Project::where('user_id', $userId)
            ->orWhereHas('users', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->get();
    }

    public function getAssignableUsers(int $userId, mixed $projectId = null): Collection
    {
        $query = User::query()->whereKeyNot($userId);

        if ($projectId) {
            $query->where(function ($builder) use ($projectId) {
                $builder->whereHas('projects', fn ($projects) => $projects->where('projects.id', $projectId))
                    ->orWhereHas('teams.projects', fn ($projects) => $projects->where('projects.id', $projectId));
            });
        }

        return $query->orderBy('name')->get();
    }

    public function getEpics(mixed $projectId = null): Collection
    {
        return Task::query()
            ->where('issue_type', 'epic')
            ->when($projectId, fn ($query) => $query->where('project_id', $projectId))
            ->orderBy('name')
            ->get();
    }

    public function getSprints(mixed $projectId = null): Collection
    {
        return \ProjectsManager\Sprint::query()
            ->when($projectId, fn ($query) => $query->where('project_id', $projectId))
            ->whereIn('status', ['planned', 'active'])
            ->orderByDesc('status')
            ->orderBy('starts_at')
            ->get();
    }

    /**
     * Get distinct labels list across tasks.
     *
     * @return array
     */
    public function getAllLabels(): array
    {
        $defaultLabels = [
            'Frontend', 'Backend', 'UI/UX', 'API', 'Database',
            'Bugfix', 'Feature', 'Refactor', 'Documentation', 'Testing', 'Urgent'
        ];

        $dbLabels = Task::query()
            ->whereNotNull('labels')
            ->where('labels', '!=', '')
            ->pluck('labels')
            ->flatMap(function ($item) {
                return array_map('trim', explode(',', $item));
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return array_values(array_unique(array_merge($defaultLabels, $dbLabels)));
    }

    /**
     * Find task by ID.
     *
     * @param int $id
     *
     * @return Task|null
     */
    public function findById(int $id): ?Task
    {
        return Task::find($id);
    }

    /**
     * Create a new task.
     *
     * @param array $data
     *
     * @return Task
     */
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    /**
     * Update existing task.
     *
     * @param Task $task
     * @param array $data
     *
     * @return bool
     */
    public function update(Task $task, array $data): bool
    {
        return $task->update($data);
    }

    /**
     * Delete a task.
     *
     * @param Task $task
     *
     * @return bool|null
     */
    public function delete(Task $task): ?bool
    {
        return $task->delete();
    }
}
