<?php

namespace ProjectsManager\Modules\Tasks\Handlers;

use ProjectsManager\Modules\Projects\Repositories\ProjectRepository;
use ProjectsManager\Modules\Tasks\Repositories\TaskRepository;
use ProjectsManager\Task;

class StoreTaskHandler
{
    /**
     * Create a new handler instance.
     *
     * @param TaskRepository $repository
     * @param ProjectRepository $projectRepository
     */
    public function __construct(
        private readonly TaskRepository $repository,
        private readonly ProjectRepository $projectRepository
    ) {
    }

    /**
     * Execute task creation use case.
     *
     * @param int $userId
     * @param array $data
     *
     * @return Task
     */
    public function handle(int $userId, array $data): Task
    {
        $project = $this->projectRepository->findById((int) $data['project_id']);

        $activeStatuses = $project ? $project->getActiveStatuses() : [];
        $defaultStatus = 'todo';
        if (!empty($activeStatuses)) {
            $initialStatus = collect($activeStatuses)->firstWhere('is_initial', true);
            $defaultStatus = $initialStatus['key'] ?? $activeStatuses[0]['key'] ?? 'todo';
        }

        $task = $this->repository->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'project_id' => $project->id,
            'user_id' => $userId,
            'status' => filled($data['status'] ?? null) ? $data['status'] : $defaultStatus,
            'priority' => $data['priority'] ?? 'medium',
            'days' => $data['days'] ?? 0,
            'hours' => $data['hours'] ?? 0,
            'company_id' => $project->company_id,
            'issue_type' => $data['issue_type'] ?? 'task',
            'assignee_id' => filled($data['assignee_id'] ?? null) ? (int) $data['assignee_id'] : null,
            'reporter_id' => filled($data['reporter_id'] ?? null) ? (int) $data['reporter_id'] : null,
            'due_date' => $data['due_date'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'story_points' => $data['story_points'] ?? null,
            'labels' => $data['labels'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'sprint_id' => $data['sprint_id'] ?? null,
        ]);

        $this->repository->update($task, [
            'issue_key' => 'TP-' . str_pad((string) $task->id, 5, '0', STR_PAD_LEFT),
        ]);

        return $task->fresh();
    }
}
