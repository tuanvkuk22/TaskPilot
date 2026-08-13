<?php

namespace ProjectsManager\Modules\Projects\Handlers;

use ProjectsManager\Project;
use ProjectsManager\Modules\Projects\Repositories\ProjectRepository;

class StoreProjectHandler
{
    /**
     * Create a new handler instance.
     *
     * @param ProjectRepository $repository
     */
    public function __construct(
        private readonly ProjectRepository $repository
    ) {
    }

    /**
     * Execute project creation use case.
     *
     * @param int $userId
     * @param array $data
     *
     * @return array
     */
    public function handle(int $userId, array $data): array
    {
        $categoryId = $data['category_id'] ?? null;
        if (empty($categoryId) && ! empty($data['category_name'])) {
            $category = $this->repository->firstOrCreateCategory($userId, (string) $data['category_name']);
            $categoryId = $category->id;
        }

        $memberEmails = $this->normalizeEmails($data['members'] ?? null);
        $teamId = $data['team_id'] ?? null;
        $teamMembers = [];

        if (! empty($teamId)) {
            $team = $this->repository->findTeamById((int) $teamId);
            if ($team) {
                foreach ($team->users as $teamUser) {
                    $teamMembers[] = strtolower($teamUser->email);
                }
            }
        }

        foreach ($memberEmails as $email) {
            if (in_array(strtolower($email), $teamMembers, true)) {
                return ['success' => false, 'error' => 'Thành viên ' . $email . ' đang nằm trong đội nhóm đã được chọn.'];
            }

            $user = $this->repository->findUserByEmail($email);
            if ($user && $this->repository->isUserAssignedToOwnerProjectsOrTeams($user->id, $userId)) {
                return ['success' => false, 'error' => 'Thành viên ' . $email . ' đã có trong một dự án hoặc đội nhóm của bạn.'];
            }
        }

        $descriptionParts = array_filter([
            trim((string) ($data['objective'] ?? '')),
            trim((string) ($data['description'] ?? '')),
        ]);

        $project = $this->repository->create([
            'name' => trim((string) ($data['name'] ?? '')),
            'description' => $descriptionParts ? implode("\n\n", $descriptionParts) : null,
            'company_id' => ! empty($data['company_id']) ? $data['company_id'] : null,
            'user_id' => $userId,
            'status' => $data['status'] ?? 'active',
            'archived' => false,
            'project_type' => $data['project_type'] ?? 'personal',
            'team_name' => null,
            'team_id' => $teamId ?: null,
            'category_id' => $categoryId,
            'project_code' => null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'budget' => $data['budget'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'customer_name' => $data['customer_name'] ?? null,
            'location' => $data['location'] ?? null,
        ]);

        if (! $project) {
            return ['success' => false, 'error' => 'Không thể tạo dự án mới.'];
        }

        $projectCode = $this->repository->generateProjectCode($project);
        $this->repository->update($project, ['project_code' => $projectCode]);

        $this->repository->attachUser($project, $userId, 'owner', ['view', 'edit', 'manage_tasks', 'manage_members', 'manage_project']);

        foreach ($memberEmails as $email) {
            $user = $this->repository->findUserByEmail($email);

            if (! $user) {
                return ['success' => true, 'project' => $project, 'warning' => 'Thành viên ' . $email . ' chưa tồn tại trong hệ thống.'];
            }

            if (! $this->repository->hasUser($project, $user->id)) {
                $this->repository->attachUser($project, $user->id, 'member', ['view', 'edit']);
            }
        }

        if (! empty($teamId)) {
            $team = $this->repository->findTeamById((int) $teamId);
            if ($team) {
                foreach ($team->users as $teamUser) {
                    if ($teamUser->id === $userId || $this->repository->hasUser($project, $teamUser->id)) {
                        continue;
                    }

                    $this->repository->attachUser($project, $teamUser->id, 'member', ['view', 'edit']);
                }
            }
        }

        return ['success' => true, 'project' => $project];
    }

    /**
     * Normalize emails input into an array.
     *
     * @param mixed $value
     *
     * @return array
     */
    private function normalizeEmails(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }

        $items = is_array($value) ? $value : preg_split('/[\r\n,;]+/', (string) $value);

        return array_values(array_unique(array_filter(array_map(function ($email) {
            return trim((string) $email);
        }, (array) $items))));
    }
}
