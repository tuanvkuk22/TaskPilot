<?php

namespace ProjectsManager\Modules\Projects\Handlers;

use ProjectsManager\Project;
use ProjectsManager\Modules\Projects\Repositories\ProjectRepository;
use ProjectsManager\ProjectRole;
use ProjectsManager\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use ProjectsManager\User;

class StoreProjectHandler
{
    public function __construct(
        private readonly ProjectRepository $repository
    ) {
    }

    public function handle(int $userId, array $data): array
    {
        $memberEmails = $this->normalizeEmails($data['members'] ?? null);
        $missingMembers = [];

        foreach ($memberEmails as $email) {
            if (! $this->repository->findUserByEmail($email)) {
                $missingMembers[] = $email;
            }
        }

        if ($missingMembers !== []) {
            return [
                'success' => false,
                'error' => 'Thành viên chưa tồn tại trong hệ thống: ' . implode(', ', $missingMembers) . '.',
            ];
        }

        $categoryId = $data['category_id'] ?? null;
        if (empty($categoryId) && ! empty($data['category_name'])) {
            $category = $this->repository->firstOrCreateCategory($userId, (string) $data['category_name']);
            $categoryId = $category->id;
        }

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
        }

        $descriptionParts = array_filter([
            trim((string) ($data['objective'] ?? '')),
            trim((string) ($data['description'] ?? '')),
        ]);

        $project = $this->repository->create([
            'name' => trim((string) ($data['name'] ?? '')),
            'description' => $descriptionParts ? implode("\n\n", $descriptionParts) : null,
            'image' => $data['image'] ?? null,
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

        $this->seedDefaultRoles($project);

        $this->repository->attachUser($project, $userId, 'owner', [
            'project.manage', 'project.edit', 'project.view',
            'task.manage', 'task.assign', 'task.view', 'task.create', 'task.edit',
            'member.manage', 'member.view', 'member.invite',
            'document.manage', 'document.view',
            'meeting.manage', 'meeting.view',
            'comment.create', 'comment.view',
            'report.view',
        ]);

        $addedMembers = [];

        foreach ($memberEmails as $email) {
            $user = $this->repository->findUserByEmail($email);

            if (! $this->repository->hasUser($project, $user->id)) {
                $this->repository->attachUser($project, $user->id, 'Thành viên', [
                    'project.view', 'project.edit',
                    'task.view', 'task.create', 'task.edit', 'task.assign',
                    'document.view', 'document.create', 'document.edit',
                    'meeting.view', 'meeting.create',
                    'comment.create', 'comment.view',
                ]);

                $addedMembers[] = $user;
            }
        }

        if (! empty($teamId)) {
            $team = $this->repository->findTeamById((int) $teamId);
            if ($team) {
                foreach ($team->users as $teamUser) {
                    if ($teamUser->id === $userId || $this->repository->hasUser($project, $teamUser->id)) {
                        continue;
                    }

                    $this->repository->attachUser($project, $teamUser->id, 'Thành viên', [
                        'project.view', 'project.edit',
                        'task.view', 'task.create', 'task.edit', 'task.assign',
                        'document.view', 'document.create', 'document.edit',
                        'meeting.view', 'meeting.create',
                        'comment.create', 'comment.view',
                    ]);

                    $addedMembers[] = $teamUser;
                }
            }
        }

        $actor = User::find($userId);

        foreach ($addedMembers as $member) {
            NotificationService::sendToUser(
                $member,
                NotificationService::EVENT_PROJECT_MEMBER_ADDED,
                'Thành viên mới trong dự án: ' . $project->name,
                $actor?->name . ' đã thêm bạn vào dự án "' . $project->name . '".',
                $actor,
                ['project_id' => $project->id, 'project_name' => $project->name],
                '/projects/' . $project->id,
            );
        }

        return ['success' => true, 'project' => $project];
    }

    private function seedDefaultRoles(Project $project): void
    {
        $defaultRoles = [
            ['name' => 'Quản lý', 'permissions' => [
                'project.manage', 'project.edit', 'project.view',
                'task.manage', 'task.assign', 'task.view', 'task.create', 'task.edit',
                'member.manage', 'member.view', 'member.invite',
                'document.manage', 'document.view',
                'meeting.manage', 'meeting.view',
                'comment.create', 'comment.view',
                'report.view',
            ]],
            ['name' => 'Thành viên', 'permissions' => [
                'project.view', 'project.edit',
                'task.view', 'task.create', 'task.edit', 'task.assign', 'task.status', 'task.priority',
                'member.view',
                'document.view', 'document.create', 'document.edit',
                'meeting.view', 'meeting.create',
                'comment.create', 'comment.view',
            ]],
            ['name' => 'Người xem', 'permissions' => [
                'project.view', 'task.view', 'member.view',
                'document.view', 'meeting.view', 'comment.view', 'report.view',
            ]],
        ];

        foreach ($defaultRoles as $role) {
            ProjectRole::create([
                'project_id' => $project->id,
                'name' => $role['name'],
                'permissions' => $role['permissions'],
            ]);
        }
    }

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
